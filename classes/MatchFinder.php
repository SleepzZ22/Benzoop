<?php
/**
 * MatchFinder Class (Smart Matching Algorithm)
 * Lost & Found Campus System
 * 
 * Analyzes items based on:
 * 1. Category alignment (40 pts)
 * 2. Location proximity / keyword similarity (25 pts)
 * 3. Date proximity (25 pts)
 * 4. Title & Description keyword overlap (10 pts)
 */

class MatchFinder {
    private PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Find potential matching items
     * 
     * @param int|array $item Target item array or item ID
     * @param int $limit Maximum number of results
     * @param int $minScore Minimum matching score (0-100)
     * @return array List of matched items with scores and reasons
     */
    public function findMatches($item, int $limit = 6, int $minScore = 25): array {
        if (is_numeric($item)) {
            $itemModel = new Item($this->db);
            $item = $itemModel->getById((int)$item);
        }

        if (!$item) {
            return [];
        }

        $sourceType     = $item['type'];
        $oppositeType   = ($sourceType === 'lost') ? 'found' : 'lost';
        $sourceCategory = (int)$item['category_id'];
        $sourceLocation = mb_strtolower(trim($item['location']), 'UTF-8');
        $sourceDate     = strtotime($item['item_date']);
        $sourceTitle    = mb_strtolower(trim($item['title']), 'UTF-8');
        $sourceDesc     = mb_strtolower(trim($item['description']), 'UTF-8');

        // Query candidate items of opposite type
        $sql = "SELECT i.*, 
                       c.name AS category_name, 
                       c.icon AS category_icon
                FROM items i
                JOIN categories c ON i.category_id = c.id
                WHERE i.type = :opposite_type 
                  AND i.id != :item_id
                  AND i.status IN ('active', 'returned')
                ORDER BY i.item_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':opposite_type' => $oppositeType,
            ':item_id'       => $item['id']
        ]);
        $candidates = $stmt->fetchAll();

        $matches = [];

        // Extract keywords from source title
        $keywords = $this->extractKeywords($sourceTitle . ' ' . $sourceDesc);

        foreach ($candidates as $candidate) {
            $score = 0;
            $reasons = [];

            // 1. Category Matching (Max 40 points)
            if ((int)$candidate['category_id'] === $sourceCategory) {
                $score += 40;
                $reasons[] = [
                    'label' => 'หมวดหมู่เดียวกัน',
                    'icon'  => 'bi-tag-fill',
                    'class' => 'bg-primary'
                ];
            }

            // 2. Location Matching (Max 25 points)
            $candLocation = mb_strtolower(trim($candidate['location']), 'UTF-8');
            $locScore = $this->calculateLocationScore($sourceLocation, $candLocation);
            if ($locScore > 0) {
                $score += $locScore;
                $reasons[] = [
                    'label' => ($locScore >= 20) ? 'สถานที่ตรงกันมาก' : 'สถานที่ใกล้เคียง',
                    'icon'  => 'bi-geo-alt-fill',
                    'class' => 'bg-info'
                ];
            }

            // 3. Date Proximity Matching (Max 25 points)
            $candDate = strtotime($candidate['item_date']);
            $dateDiffDays = abs(($candDate - $sourceDate) / 86400);

            if ($dateDiffDays <= 1) {
                $score += 25;
                $reasons[] = [
                    'label' => ($dateDiffDays === 0.0) ? 'วันที่ตรงกันพอดี' : 'ห่างกันเพียง 1 วัน',
                    'icon'  => 'bi-calendar-check-fill',
                    'class' => 'bg-success'
                ];
            } elseif ($dateDiffDays <= 3) {
                $score += 20;
                $reasons[] = [
                    'label' => 'ช่วงเวลาใกล้กัน (ไม่เกิน 3 วัน)',
                    'icon'  => 'bi-calendar-event',
                    'class' => 'bg-success'
                ];
            } elseif ($dateDiffDays <= 7) {
                $score += 15;
                $reasons[] = [
                    'label' => 'ช่วงเวลาในสัปดาห์เดียวกัน',
                    'icon'  => 'bi-calendar3',
                    'class' => 'bg-secondary'
                ];
            } elseif ($dateDiffDays <= 14) {
                $score += 8;
            }

            // 4. Keyword Overlap (Max 10 points)
            $candText = mb_strtolower($candidate['title'] . ' ' . $candidate['description'], 'UTF-8');
            $matchedWordCount = 0;
            foreach ($keywords as $word) {
                if (mb_strpos($candText, $word, 0, 'UTF-8') !== false) {
                    $matchedWordCount++;
                }
            }

            if ($matchedWordCount > 0) {
                $keywordScore = min(10, $matchedWordCount * 4);
                $score += $keywordScore;
                $reasons[] = [
                    'label' => "คำสำคัญตรงกัน ($matchedWordCount คำ)",
                    'icon'  => 'bi-search',
                    'class' => 'bg-warning text-dark'
                ];
            }

            // Keep candidate if score reaches threshold
            if ($score >= $minScore) {
                $candidate['match_score'] = min(100, $score);
                $candidate['match_reasons'] = $reasons;
                $matches[] = $candidate;
            }
        }

        // Sort by match score descending
        usort($matches, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return array_slice($matches, 0, $limit);
    }

    /**
     * Compute location match score between two strings
     */
    private function calculateLocationScore(string $loc1, string $loc2): int {
        if ($loc1 === $loc2) {
            return 25;
        }

        // Check if one contains the other
        if (mb_strpos($loc1, $loc2, 0, 'UTF-8') !== false || mb_strpos($loc2, $loc1, 0, 'UTF-8') !== false) {
            return 22;
        }

        // Common campus key landmarks
        $landmarks = [
            'หอสมุด', 'โรงอาหาร', 'อาคารเรียนรวม', 'ตึกวิศวะ', 'ตึกวิทย์', 
            'สนามกีฬา', 'ลานจอดรถ', 'ประตู 1', 'ประตู 2', 'หอพัก', 'ตึก 1', 'ตึก 2', 'ตึก 3', 'ตึก 4'
        ];

        foreach ($landmarks as $landmark) {
            if (mb_strpos($loc1, $landmark, 0, 'UTF-8') !== false && mb_strpos($loc2, $landmark, 0, 'UTF-8') !== false) {
                return 18;
            }
        }

        // Similar_text comparison
        similar_text($loc1, $loc2, $percent);
        if ($percent >= 60) {
            return 15;
        } elseif ($percent >= 40) {
            return 10;
        }

        return 0;
    }

    /**
     * Extract meaningful search keywords (filter out stop words)
     */
    private function extractKeywords(string $text): array {
        // Strip punctuation and special chars
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $stopWords = [
            'ทำ', 'หาย', 'พบ', 'ได้', 'ที่', 'ลืม', 'ไว้', 'ของ', 'มี', 'อยู่', 'เป็น', 
            'และ', 'หรือ', 'แถว', 'บริเวณ', 'ตรง', 'ประมาณ', 'ช่วง', 'เวลา', 'ครับ', 'ค่ะ',
            'รบกวน', 'ติดต่อ', 'สี', 'ยี่ห้อ', 'รุ่น'
        ];

        $keywords = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w, 'UTF-8') >= 3 && !in_array($w, $stopWords)) {
                $keywords[] = $w;
            }
        }

        return array_unique($keywords);
    }
}
