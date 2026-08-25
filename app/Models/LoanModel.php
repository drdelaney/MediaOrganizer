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
     * Get active loan for media
     */
    public function getActiveLoanForMedia($mediaId)
    {
        return $this->select('loans.*, people.name as person_name, people.email, people.phone, people.notifications')
            ->join('people', 'people.person_id = loans.person_id')
            ->where('loans.movie_id', $mediaId)
            ->where('loans.return_date IS NULL')
            ->first();
    }

    /**
     * Get loan history for media
     */
    public function getLoanHistoryForMedia($mediaId)
    {
        return $this->select('loans.*, people.name as person_name, people.email, people.phone, people.notifications')
            ->join('people', 'people.person_id = loans.person_id')
            ->where('loans.movie_id', $mediaId)
            ->orderBy('loans.date', 'DESC')
            ->findAll();
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
     * Get all currently loaned media with details (including orphaned records)
     */
    public function getAllLoanedMedia()
    {
        // First get all media marked as loaned
        $mediaModel = new \App\Models\MediaModel();
        $loanedMovies = $mediaModel->where('loaned', 1)->findAll();
        
        if (empty($loanedMovies)) {
            return [];
        }

        $results = [];
        foreach ($loanedMovies as $movie) {
            // Try to find the active loan record for each
            $loan = $this->select('loans.*, 
                                 people.person_id, people.name as person_name, 
                                 people.email as person_email, people.phone as person_phone,
                                 people.notifications as person_notifications')
                ->join('people', 'people.person_id = loans.person_id', 'left')
                ->where('loans.movie_id', $movie['movie_id'])
                ->where('loans.return_date IS NULL')
                ->first();

            // Get medium name if movie has medium_id
            $mediumName = '';
            if (!empty($movie['medium_id'])) {
                $db = \Config\Database::connect();
                $medium = $db->table('media')->where('medium_id', $movie['medium_id'])->get()->getRowArray();
                $mediumName = $medium['name'] ?? '';
            }

            if ($loan) {
                $results[] = array_merge($movie, $loan, ['medium_name' => $mediumName]);
            } else {
                // Orphaned loan: media is marked as loaned but no record in loans table
                $results[] = array_merge($movie, [
                    'loan_id' => null,
                    'person_id' => null,
                    'person_name' => 'Unknown (Orphaned Record)',
                    'person_email' => null,
                    'person_phone' => null,
                    'person_notifications' => 1,
                    'date' => null,
                    'medium_name' => $mediumName
                ]);
            }
        }

        // Sort by date descending (nulls last)
        usort($results, function($a, $b) {
            if ($a['date'] === $b['date']) return 0;
            if ($a['date'] === null) return 1;
            if ($b['date'] === null) return -1;
            return strcmp($b['date'], $a['date']);
        });

        return $results;
    }

    /**
     * Loan media to a person
     */
    public function loanMedia($mediaId, $personId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Check if media is already loaned
        $existingLoan = $this->where('movie_id', $mediaId)
            ->where('return_date IS NULL')
            ->first();

        if ($existingLoan) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Media is already loaned out'];
        }

        // Check if media is tagged with "wishlist"
        $tagModel = new \App\Models\TagModel();
        $tags = $tagModel->getTagsForMedia($mediaId);
        $isWishlist = false;
        foreach ($tags as $tag) {
            if (strtolower($tag['name']) === 'wishlist') {
                $isWishlist = true;
                break;
            }
        }

        if ($isWishlist) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Cannot loan media that is on the wishlist'];
        }

        // Create loan record
        $loanData = [
            'movie_id' => $mediaId,
            'person_id' => $personId,
            'date' => gmdate('Y-m-d'),
            'return_date' => null
        ];

        if (!$this->insert($loanData)) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to create loan record'];
        }

        // Update media loaned status
        $mediaModel = new \App\Models\MediaModel();
        if (!$mediaModel->update($mediaId, ['loaned' => 1])) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to update media status'];
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        return ['success' => true, 'message' => 'Media loaned successfully'];
    }

    /**
     * Return loaned media
     */
    public function returnMedia($mediaId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Find active loan
        $loan = $this->where('movie_id', $mediaId)
            ->where('return_date IS NULL')
            ->first();

        // Update media loaned status anyway if it's marked as loaned
        $movieModel = new \App\Models\MediaModel();
        if (!$movieModel->update($mediaId, ['loaned' => 0])) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Failed to update media status'];
        }

        if ($loan) {
            // Update loan with return date
            if (!$this->update($loan['loan_id'], ['return_date' => gmdate('Y-m-d')])) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Failed to update loan record'];
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Transaction failed'];
        }

        if (!$loan) {
            return ['success' => true, 'message' => 'Media status was fixed (no active loan record found)'];
        }

        return ['success' => true, 'message' => 'Media returned successfully'];
    }
}
