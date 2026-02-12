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
        
        // Find posters not used in movies table using Query Builder
        $subQuery = $db->table('movies')
            ->select('poster_md5')
            ->where('poster_md5 IS NOT NULL')
            ->distinct();
        
        $db->table('posters')
            ->whereNotIn('md5sum', $subQuery)
            ->delete();
        
        return $db->affectedRows();
    }
}
