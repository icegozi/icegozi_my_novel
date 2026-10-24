<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\AuthorProfile;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\ImportJob;
use App\Models\Novel;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportValidationService
{
    /**
     * Standard CSV column schemas.
     */
    protected const NOVEL_HEADERS = [
        'tieu_de',
        'but_danh_tac_gia',
        'the_loai',
        'tom_tat',
        'do_tuoi',
        'ban_quyen',
        'tu_khoa_seo',
        'meta_description',
    ];

    protected const CHAPTER_HEADERS = [
        'slug_truyen',
        'so_chuong',
        'tieu_de_chuong',
        'noi_dung',
    ];

    /**
     * Generate standard CSV template string with UTF-8 BOM.
     */
    public function generateTemplate(string $type): string
    {
        $bom = "\xEF\xBB\xBF";

        if ($type === 'chapters') {
            $header = implode(',', self::CHAPTER_HEADERS) . "\n";
            $sample = "thien-dao-kiem-ton,1.0,Chương 1: Xuất Thế,\"<p>Đêm đen bao trùm lấy chân trời kiếm khí ngập tràn...</p>\"\n";
            return $bom . $header . $sample;
        }

        $header = implode(',', self::NOVEL_HEADERS) . "\n";
        $sample = "Thiên Đạo Kiếm Tôn,Hắc Miêu Tiên Sinh,Tiên Hiệp,Một câu chuyện kiếm tu kỳ ảo xuất chúng,all,original,kiem-hiep,Doc truyen Thien Dao Kiem Ton\n";
        return $bom . $header . $sample;
    }

    /**
     * Validate CSV file and parse valid rows without writing to DB.
     *
     * @return array{valid: bool, total_rows: int, errors: array, parsed_data: array}
     */
    public function validateCsv(string $filePath, string $type): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [
                'valid' => false,
                'total_rows' => 0,
                'errors' => [['row' => 0, 'column' => 'file', 'message' => 'Không thể mở tệp CSV để phân tích.']],
                'parsed_data' => [],
            ];
        }

        // 1. Read and clean headers
        $rawHeaders = fgetcsv($handle);
        if (!$rawHeaders) {
            fclose($handle);
            return [
                'valid' => false,
                'total_rows' => 0,
                'errors' => [['row' => 1, 'column' => 'header', 'message' => 'Tệp CSV trống hoặc không có dòng tiêu đề.']],
                'parsed_data' => [],
            ];
        }

        // Strip UTF-8 BOM from first header token
        $rawHeaders[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeaders[0]);
        $headers = array_map('trim', $rawHeaders);

        $expectedHeaders = $type === 'chapters' ? self::CHAPTER_HEADERS : self::NOVEL_HEADERS;

        // Check required column presence
        $missing = array_diff($expectedHeaders, $headers);
        if (!empty($missing)) {
            fclose($handle);
            return [
                'valid' => false,
                'total_rows' => 0,
                'errors' => [[
                    'row' => 1,
                    'column' => 'header',
                    'message' => 'Tệp CSV thiếu các cột bắt buộc: ' . implode(', ', $missing),
                ]],
                'parsed_data' => [],
            ];
        }

        // Cache DB reference sets to avoid N+1 queries during file scanning
        $authors = AuthorProfile::pluck('user_id', 'pen_name')->toArray();
        $genres = Genre::pluck('id', 'name')->toArray();
        $novels = Novel::pluck('id', 'slug')->toArray();

        $errors = [];
        $parsedData = [];
        $rowIndex = 1; // Row 1 is header

        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $rowData = array_combine($headers, array_map('trim', array_slice($row, 0, count($headers))));

            if ($type === 'chapters') {
                $this->validateChapterRow($rowData, $rowIndex, $novels, $errors, $parsedData);
            } else {
                $this->validateNovelRow($rowData, $rowIndex, $authors, $genres, $errors, $parsedData);
            }
        }

        fclose($handle);

        return [
            'valid' => empty($errors),
            'total_rows' => count($parsedData) + count($errors),
            'errors' => $errors,
            'parsed_data' => $parsedData,
        ];
    }

    /**
     * Validate a single row for novel import.
     */
    protected function validateNovelRow(
        array $row,
        int $rowIndex,
        array $authors,
        array $genres,
        array &$errors,
        array &$parsedData
    ): void {
        $title = $row['tieu_de'] ?? '';
        $penName = $row['but_danh_tac_gia'] ?? '';
        $genreNames = $row['the_loai'] ?? '';
        $synopsis = $row['tom_tat'] ?? '';

        if ($title === '') {
            $errors[] = ['row' => $rowIndex, 'column' => 'tieu_de', 'message' => "Dòng {$rowIndex}: Tiêu đề tác phẩm không được để trống."];
            return;
        }

        if ($penName === '' || !isset($authors[$penName])) {
            $errors[] = ['row' => $rowIndex, 'column' => 'but_danh_tac_gia', 'message' => "Dòng {$rowIndex}: Bút danh tác giả '{$penName}' không tồn tại trên hệ thống."];
            return;
        }

        if ($synopsis === '') {
            $errors[] = ['row' => $rowIndex, 'column' => 'tom_tat', 'message' => "Dòng {$rowIndex}: Tóm tắt truyện không được để trống."];
            return;
        }

        $genreList = array_filter(array_map('trim', explode(',', $genreNames)));
        $genreIds = [];
        foreach ($genreList as $gName) {
            if (isset($genres[$gName])) {
                $genreIds[] = $genres[$gName];
            }
        }

        $parsedData[] = [
            'author_id' => $authors[$penName],
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'synopsis' => $synopsis,
            'age_rating' => in_array($row['do_tuoi'] ?? '', ['all', '13_plus', '16_plus', '18_plus']) ? $row['do_tuoi'] : 'all',
            'copyright_type' => in_array($row['ban_quyen'] ?? '', ['original', 'authorized_translation', 'public_domain']) ? $row['ban_quyen'] : 'original',
            'meta_title' => $row['meta_title'] ?? $title,
            'meta_description' => $row['meta_description'] ?? Str::limit($synopsis, 160),
            'genre_ids' => $genreIds,
            'tags' => array_filter(array_map('trim', explode(',', $row['tu_khoa_seo'] ?? ''))),
        ];
    }

    /**
     * Validate a single row for chapter import.
     */
    protected function validateChapterRow(
        array $row,
        int $rowIndex,
        array $novels,
        array &$errors,
        array &$parsedData
    ): void {
        $slugTruyen = $row['slug_truyen'] ?? '';
        $soChuong = $row['so_chuong'] ?? '';
        $tieuDe = $row['tieu_de_chuong'] ?? '';
        $noiDung = $row['noi_dung'] ?? '';

        if (!isset($novels[$slugTruyen])) {
            $errors[] = ['row' => $rowIndex, 'column' => 'slug_truyen', 'message' => "Dòng {$rowIndex}: Slug truyện '{$slugTruyen}' không tồn tại."];
            return;
        }

        if (!is_numeric($soChuong)) {
            $errors[] = ['row' => $rowIndex, 'column' => 'so_chuong', 'message' => "Dòng {$rowIndex}: Số chương '{$soChuong}' phải là định dạng số."];
            return;
        }

        if ($tieuDe === '') {
            $errors[] = ['row' => $rowIndex, 'column' => 'tieu_de_chuong', 'message' => "Dòng {$rowIndex}: Tiêu đề chương không được để trống."];
            return;
        }

        if ($noiDung === '') {
            $errors[] = ['row' => $rowIndex, 'column' => 'noi_dung', 'message' => "Dòng {$rowIndex}: Nội dung chương không được để trống."];
            return;
        }

        $plainText = trim(preg_replace('/\s+/', ' ', strip_tags($noiDung)));
        $wordCount = $plainText !== '' ? count(explode(' ', $plainText)) : 0;

        $parsedData[] = [
            'novel_id' => $novels[$slugTruyen],
            'chapter_number' => (float) $soChuong,
            'title' => $tieuDe,
            'slug' => 'chuong-' . Str::slug($soChuong . '-' . $tieuDe),
            'content' => $noiDung,
            'word_count' => $wordCount,
        ];
    }

    /**
     * Generate an error report CSV string with UTF-8 BOM for user download.
     */
    public function generateErrorReportCsv(array $errors): string
    {
        $output = "\xEF\xBB\xBF" . "Dong,Cot,Nguyen_Nhan_Loi\n";
        foreach ($errors as $err) {
            $rowNum = $err['row'] ?? 'N/A';
            $colName = $err['column'] ?? 'N/A';
            $msg = str_replace('"', '""', $err['message'] ?? '');
            $output .= "{$rowNum},{$colName},\"{$msg}\"\n";
        }
        return $output;
    }

    /**
     * Commit validated import data into the database inside a transaction.
     * All imported entities are guaranteed to be created as 'draft'.
     */
    public function commitImport(ImportJob $job, array $parsedData, User $user): int
    {
        return DB::transaction(function () use ($job, $parsedData, $user) {
            $job->update(['status' => 'importing']);
            $count = 0;

            if ($job->type === 'novels') {
                foreach ($parsedData as $data) {
                    $genreIds = $data['genre_ids'] ?? [];
                    $tagNames = $data['tags'] ?? [];
                    unset($data['genre_ids'], $data['tags']);

                    $data['status'] = 'draft'; // Strict safety requirement: Always draft!
                    $novel = Novel::create($data);

                    if (!empty($genreIds)) {
                        $novel->genres()->sync($genreIds);
                    }

                    if (!empty($tagNames)) {
                        $tagIds = [];
                        foreach ($tagNames as $tName) {
                            $tag = Tag::firstOrCreate(
                                ['slug' => Str::slug($tName)],
                                ['name' => $tName]
                            );
                            $tagIds[] = $tag->id;
                        }
                        $novel->tags()->sync($tagIds);
                    }

                    $count++;
                }
            } elseif ($job->type === 'chapters') {
                foreach ($parsedData as $data) {
                    $data['status'] = 'draft'; // Always draft!
                    $data['revision'] = 1;
                    Chapter::create($data);
                    $count++;
                }
            }

            $job->update([
                'status' => 'completed',
                'successful_rows' => $count,
                'total_rows' => $count,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'admin_bulk_import_committed',
                'target_type' => 'ImportJob',
                'target_id' => $job->id,
                'old_values' => ['status' => 'validated'],
                'new_values' => [
                    'status' => 'completed',
                    'type' => $job->type,
                    'successful_rows' => $count,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return $count;
        });
    }
}
