<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        // 1. achannels
        $achannels = [
            ['achannel_id' => 1, 'name' => 'mono'],
            ['achannel_id' => 2, 'name' => 'stereo'],
            ['achannel_id' => 3, 'name' => '5.1'],
            ['achannel_id' => 4, 'name' => '7.1'],
        ];
        $this->db->table('achannels')->insertBatch($achannels);

        // 2. acodecs
        $acodecs = [
            ['acodec_id' => 1, 'name' => 'AC-3 Dolby audio'],
            ['acodec_id' => 2, 'name' => 'OGG'],
            ['acodec_id' => 3, 'name' => 'MP3'],
            ['acodec_id' => 4, 'name' => 'MPEG-1'],
            ['acodec_id' => 5, 'name' => 'MPEG-2'],
            ['acodec_id' => 6, 'name' => 'AAC'],
            ['acodec_id' => 7, 'name' => 'Windows Media Audio'],
        ];
        $this->db->table('acodecs')->insertBatch($acodecs);

        // 3. configuration
        $configuration = [
            ['param' => 'version', 'value' => '7'],
        ];
        foreach ($configuration as $row) {
            if ($this->db->table('configuration')->where('param', $row['param'])->countAllResults() === 0) {
                $this->db->table('configuration')->insert($row);
            } else {
                $this->db->table('configuration')->where('param', $row['param'])->update($row);
            }
        }

        // 4. languages
        $languages = [
            ['lang_id' => 1, 'name' => 'Brazilian Portuguese'],
            ['lang_id' => 2, 'name' => 'Bulgarian'],
            ['lang_id' => 3, 'name' => 'Catalan'],
            ['lang_id' => 4, 'name' => 'Czech'],
            ['lang_id' => 5, 'name' => 'Danish'],
            ['lang_id' => 6, 'name' => 'Dutch'],
            ['lang_id' => 7, 'name' => 'English'],
            ['lang_id' => 8, 'name' => 'Estonian'],
            ['lang_id' => 9, 'name' => 'French'],
            ['lang_id' => 10, 'name' => 'German'],
            ['lang_id' => 11, 'name' => 'Greek'],
            ['lang_id' => 12, 'name' => 'Hungarian'],
            ['lang_id' => 13, 'name' => 'Indonesian'],
            ['lang_id' => 14, 'name' => 'Italian'],
            ['lang_id' => 15, 'name' => 'Japanese'],
            ['lang_id' => 16, 'name' => 'Korean'],
            ['lang_id' => 17, 'name' => 'Norwegian Bokmal'],
            ['lang_id' => 18, 'name' => 'Occitan'],
            ['lang_id' => 19, 'name' => 'Pashto'],
            ['lang_id' => 20, 'name' => 'Polish'],
            ['lang_id' => 21, 'name' => 'Portuguese'],
            ['lang_id' => 22, 'name' => 'Russian'],
            ['lang_id' => 23, 'name' => 'Simplified Chinese'],
            ['lang_id' => 24, 'name' => 'Slovak'],
            ['lang_id' => 25, 'name' => 'Spanish'],
            ['lang_id' => 26, 'name' => 'Swedish'],
            ['lang_id' => 27, 'name' => 'Turkish'],
        ];
        $this->db->table('languages')->insertBatch($languages);

        // 5. media
        $media = [
            ['medium_id' => 1, 'name' => 'DVD'],
            ['medium_id' => 2, 'name' => 'DVD-R'],
            ['medium_id' => 3, 'name' => 'DVD-RW'],
            ['medium_id' => 4, 'name' => 'DVD+R'],
            ['medium_id' => 5, 'name' => 'DVD+RW'],
            ['medium_id' => 6, 'name' => 'DVD-RAM'],
            ['medium_id' => 7, 'name' => 'CD'],
            ['medium_id' => 8, 'name' => 'CD-RW'],
            ['medium_id' => 9, 'name' => 'VCD'],
            ['medium_id' => 10, 'name' => 'SVCD'],
            ['medium_id' => 11, 'name' => 'VHS'],
            ['medium_id' => 12, 'name' => 'BETACAM'],
            ['medium_id' => 13, 'name' => 'LaserDisc'],
            ['medium_id' => 14, 'name' => 'HD DVD'],
            ['medium_id' => 15, 'name' => 'Blu-ray'],
            ['medium_id' => 16, 'name' => '4K Blu-ray'],
        ];
        $this->db->table('media')->insertBatch($media);

        // 6. ratios
        $ratios = [
            ['ratio_id' => 1, 'name' => '16:9'],
            ['ratio_id' => 2, 'name' => '16:10'],
            ['ratio_id' => 3, 'name' => '4:3'],
        ];
        $this->db->table('ratios')->insertBatch($ratios);

        // 7. subformats
        $subformats = [
            ['subformat_id' => 1, 'name' => 'DVD VOB'],
            ['subformat_id' => 2, 'name' => 'MPL2 (.txt)'],
            ['subformat_id' => 3, 'name' => 'MicroDVD (.sub)'],
            ['subformat_id' => 4, 'name' => 'SubRip (.srt)'],
            ['subformat_id' => 5, 'name' => 'SubViewer2 (.sub)'],
            ['subformat_id' => 6, 'name' => 'Sub Station Alpha (.ssa)'],
            ['subformat_id' => 7, 'name' => 'Advanced Sub Station Alpha (.ssa)'],
        ];
        $this->db->table('subformats')->insertBatch($subformats);

        // 8. tags
        $tags = [
            ['tag_id' => 1, 'name' => 'Favorite'],
            ['tag_id' => 2, 'name' => 'Wishlist'],
        ];
        $this->db->table('tags')->insertBatch($tags);

        // 9. vcodecs
        $vcodecs = [
            ['vcodec_id' => 1, 'name' => 'MPEG-1'],
            ['vcodec_id' => 2, 'name' => 'MPEG-2'],
            ['vcodec_id' => 3, 'name' => 'XviD'],
            ['vcodec_id' => 4, 'name' => 'DivX'],
            ['vcodec_id' => 5, 'name' => 'H.264'],
            ['vcodec_id' => 6, 'name' => 'RealVideo'],
            ['vcodec_id' => 7, 'name' => 'QuickTime'],
            ['vcodec_id' => 8, 'name' => 'Windows Media Video'],
        ];
        $this->db->table('vcodecs')->insertBatch($vcodecs);
    }
}
