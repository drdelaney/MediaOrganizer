<?php

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'tag_id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    protected $validationRules = [
        'tag_id' => 'permit_empty|is_natural_no_zero',
        'name' => 'required|max_length[64]|is_unique[tags.name,tag_id,{tag_id}]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Tag name is required',
            'is_unique' => 'This tag name already exists',
            'max_length' => 'Tag name cannot exceed 64 characters'
        ]
    ];

    /**
     * Get all tags for a specific media
     */
    public function getTagsForMedia($mediaId)
    {
        return $this->db->table('movie_tag mt')
            ->select('t.tag_id, t.name')
            ->join('tags t', 't.tag_id = mt.tag_id')
            ->where('mt.movie_id', $mediaId)
            ->orderBy('t.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Add a tag to media
     */
    public function addTagToMovie($mediaId, $tagId)
    {
        // Check if the relationship already exists
        $existing = $this->db->table('movie_tag')
            ->where('movie_id', $mediaId)
            ->where('tag_id', $tagId)
            ->get()
            ->getRowArray();

        if ($existing) {
            return false; // Already exists
        }

        return $this->db->table('movie_tag')->insert([
            'movie_id' => $mediaId,
            'tag_id' => $tagId
        ]);
    }

    /**
     * Remove a tag from media
     */
    public function removeTagFromMovie($mediaId, $tagId)
    {
        return $this->db->table('movie_tag')
            ->where('movie_id', $mediaId)
            ->where('tag_id', $tagId)
            ->delete();
    }

    /**
     * Set tags for media (replaces all existing tags)
     */
    public function setTagsForMedia($mediaId, array $tagIds)
    {
        // Remove all existing tags for this media
        $this->db->table('movie_tag')
            ->where('movie_id', $mediaId)
            ->delete();

        // Add new tags
        if (!empty($tagIds)) {
            $data = [];
            foreach ($tagIds as $tagId) {
                $data[] = [
                    'movie_id' => $mediaId,
                    'tag_id' => $tagId
                ];
            }
            return $this->db->table('movie_tag')->insertBatch($data);
        }

        return true;
    }

    /**
     * Get count of media using this tag
     */
    public function getMediaCountForTag($tagId)
    {
        return $this->db->table('movie_tag')
            ->where('tag_id', $tagId)
            ->countAllResults();
    }

    /**
     * Check if tag can be safely deleted
     */
    public function canDelete($tagId)
    {
        $count = $this->getMediaCountForTag($tagId);
        return $count === 0;
    }
}
