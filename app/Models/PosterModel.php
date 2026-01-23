<?php
namespace App\Models;

use CodeIgniter\Model;

class PosterModel extends Model
{
    protected $table = 'posters';
    protected $primaryKey = 'md5sum';
    protected $allowedFields = ['md5sum', 'data'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    /**
     * Delete unused posters
     */
    public function purgeUnused()
    {
        $db = \Config\Database::connect();
        
        // Find posters not used in movies table
        $query = "DELETE FROM posters WHERE md5sum NOT IN (SELECT DISTINCT poster_md5 FROM movies WHERE poster_md5 IS NOT NULL)";
        $db->query($query);
        
        return $db->affectedRows();
    }
}
