<?php

namespace App\Services;

use App\Helpers\FormulaSanitizerHelper;
use App\Models\AuthorProfile;
use App\Models\Novel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataStreamingExportService
{
    /**
     * Column mappings for novel export.
     */
    public const NOVEL_COLUMNS = [
        'id' => 'ID',
        'title' => 'Tiêu đề tác phẩm',
        'slug' => 'Slug',
        'author' => 'Bút danh tác giả',
        'genres' => 'Thể loại',
        'status' => 'Trạng thái xuất bản',
        'total_words' => 'Tổng số từ',
        'total_chapters' => 'Tổng số chương',
        'view_count' => 'Lượt xem',
        'created_at' => 'Ngày tạo',
    ];

    /**
     * Column mappings for author export.
     */
    public const AUTHOR_COLUMNS = [
        'id' => 'ID',
        'pen_name' => 'Bút danh',
        'legal_full_name' => 'Họ và tên thật',
        'email' => 'Email',
        'verification_status' => 'Trạng thái KYC',
        'user_status' => 'Trạng thái tài khoản',
        'tax_code' => 'Mã số thuế',
        'bank_name' => 'Ngân hàng',
        'bank_account_number' => 'Số tài khoản',
        'created_at' => 'Ngày đăng ký',
    ];

    /**
     * Stream novel data to CSV output with cursor-based memory safety and formula sanitization.
     */
    public function streamNovels(array $filters = [], array $selectedColumns = []): StreamedResponse
    {
        $columns = empty($selectedColumns) ? array_keys(self::NOVEL_COLUMNS) : $selectedColumns;
        $headers = array_intersect_key(self::NOVEL_COLUMNS, array_flip($columns));

        $query = Novel::query()->with(['author.authorProfile', 'genres'])->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['genre_id'])) {
            $genreId = $filters['genre_id'];
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $filename = 'xuat_du_lieu_tac_pham_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $columns, $headers) {
            $output = fopen('php://output', 'w');

            // 1. Write UTF-8 BOM
            fwrite($output, "\xEF\xBB\xBF");

            // 2. Write CSV Header
            fputcsv($output, array_values($headers));

            // 3. Stream data row by row in eager-loaded chunks to eliminate N+1 queries
            foreach ($query->lazy(500) as $novel) {
                $row = [];
                foreach ($columns as $col) {
                    $val = match ($col) {
                        'id' => $novel->id,
                        'title' => $novel->title,
                        'slug' => $novel->slug,
                        'author' => $novel->author?->authorProfile?->pen_name ?? $novel->author?->name ?? 'N/A',
                        'genres' => $novel->genres->pluck('name')->implode(', '),
                        'status' => $novel->status,
                        'total_words' => $novel->total_words,
                        'total_chapters' => $novel->total_chapters,
                        'view_count' => $novel->view_count,
                        'created_at' => $novel->created_at?->format('Y-m-d H:i:s') ?? '',
                        default => '',
                    };
                    $row[$col] = $val;
                }

                // Sanitize against CSV Formula Injection
                $sanitizedRow = FormulaSanitizerHelper::sanitizeRow($row);
                fputcsv($output, array_values($sanitizedRow));
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Stream author data to CSV with sensitive data masking and formula sanitization.
     */
    public function streamAuthors(array $filters = [], array $selectedColumns = []): StreamedResponse
    {
        $columns = empty($selectedColumns) ? array_keys(self::AUTHOR_COLUMNS) : $selectedColumns;
        $headers = array_intersect_key(self::AUTHOR_COLUMNS, array_flip($columns));

        $query = AuthorProfile::query()->with('user')->latest();

        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (!empty($filters['user_status'])) {
            $status = $filters['user_status'];
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $filename = 'xuat_du_lieu_tac_gia_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $columns, $headers) {
            $output = fopen('php://output', 'w');

            // 1. Write UTF-8 BOM
            fwrite($output, "\xEF\xBB\xBF");

            // 2. Write CSV Header
            fputcsv($output, array_values($headers));

            // 3. Stream data row by row in eager-loaded chunks to eliminate N+1 queries
            foreach ($query->lazy(500) as $author) {
                $row = [];
                foreach ($columns as $col) {
                    $val = match ($col) {
                        'id' => $author->id,
                        'pen_name' => $author->pen_name,
                        'legal_full_name' => $author->legal_full_name,
                        'email' => $author->user?->email ?? '',
                        'verification_status' => $author->verification_status,
                        'user_status' => $author->user?->status ?? '',
                        'tax_code' => $author->tax_code ?? '',
                        'bank_name' => $author->bank_name ?? '',
                        'bank_account_number' => $author->bank_account_number ?? '',
                        'created_at' => $author->created_at?->format('Y-m-d H:i:s') ?? '',
                        default => '',
                    };
                    $row[$col] = $val;
                }

                $sanitizedRow = FormulaSanitizerHelper::sanitizeRow($row);
                fputcsv($output, array_values($sanitizedRow));
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
