<?php
namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table = 'loans';
    protected $primaryKey = 'loan_id';
    protected $allowedFields = ['person_id', 'movie_id', 'volume_id', 'collection_id', 'date', 'return_date'];
    protected $useTimestamps = false;

    /**
     * Get active loan for a movie
     */
    public function getActiveLoanForMovie($movieId)
    {
        return $this->select('loans.*, people.name as person_name, people.email, people.phone')
            ->join('people', 'people.person_id = loans.person_id')
            ->where('loans.movie_id', $movieId)
            ->where('loans.return_date IS NULL')
            ->first();
    }

    /**
     * Get all active loans for a person
     */
    public function getActiveLoansForPerson($personId)
    {
        return $this->select('loans.*, movies.title, movies.o_title, movies.number')
            ->join('movies', 'movies.movie_id = loans.movie_id')
            ->where('loans.person_id', $personId)
            ->where('loans.return_date IS NULL')
            ->findAll();
    }

    /**
     * Get all currently loaned movies with details
     */
    public function getAllLoanedMovies()
    {
        return $this->select('loans.*, 
                             movies.movie_id, movies.title, movies.o_title, movies.year, 
                             movies.director, movies.genre, movies.poster_md5,
                             people.person_id, people.name as person_name, 
                             people.email as person_email, people.phone as person_phone,
                             media.name as medium_name')
            ->join('movies', 'movies.movie_id = loans.movie_id')
            ->join('people', 'people.person_id = loans.person_id')
            ->join('media', 'movies.medium_id = media.medium_id', 'left')
            ->where('loans.return_date IS NULL')
            ->orderBy('loans.date', 'DESC')
            ->findAll();
    }

    /**
     * Loan a movie to a person
     */
    public function loanMovie($movieId, $personId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Check if movie is already loaned
        $existingLoan = $this->where('movie_id', $movieId)
            ->where('return_date IS NULL')
            ->first();

        if ($existingLoan) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Movie is already loaned out'];
        }

        // Create loan record
        $loanData = [
            'movie_id' => $movieId,
            'person_id' => $personId,
            'date' => date('Y-m-d'),
            'return_date' => null
        ];

        if (!$this->insert($loanData)) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to create loan record'];
        }

        // Update movie loaned status
        $movieModel = new \App\Models\MovieModel();
        if (!$movieModel->update($movieId, ['loaned' => 1])) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to update movie status'];
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Movie loaned successfully'];
    }

    /**
     * Return a loaned movie
     */
    public function returnMovie($movieId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Find active loan
        $loan = $this->where('movie_id', $movieId)
            ->where('return_date IS NULL')
            ->first();

        if (!$loan) {
            $db->transRollback();
            return ['success' => false, 'message' => 'No active loan found for this movie'];
        }

        // Update loan with return date
        if (!$this->update($loan['loan_id'], ['return_date' => date('Y-m-d')])) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to update loan record'];
        }

        // Update movie loaned status
        $movieModel = new \App\Models\MovieModel();
        if (!$movieModel->update($movieId, ['loaned' => 0])) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to update movie status'];
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Movie returned successfully'];
    }
}
