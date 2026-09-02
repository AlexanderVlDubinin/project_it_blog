<?php

namespace App\Filament\Widgets;

use App\Models\Like;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LikesTrendChart extends ChartWidget
{
    // Widget sorting - the order of widget output
    protected static ?int $sort = 10;

    // Chart title
    protected ?string $heading = 'Reaction analysis';

    // Specify the chart type: line (linear) or bar (columnar)
    protected static string $type = 'line';

    /**
     * The column span of the widget (full, 1/2, 1/3, 1/4, 1/6, 1/12).
     */
    protected int | string | array $columnSpan = 'full';

    // 1. Default filter value
    public ?string $filter = 'month';

    // 2. The method that creates a drop-down list at the top of the card
    protected function getFilters(): ?array
    {
        return [
            'week' => 'In a week',
            'month' => 'Per month',
            'year' => 'For the year',
        ];
    }

    protected function getData(): array
    {
        // 3. Defining the time period
        $startDate = match ($this->filter) {
            'week' => Carbon::now()->subDays(7)->startOfDay(),
            'year' => Carbon::now()->subYears(1)->startOfMonth(),
            default => Carbon::now()->subDays(30)->startOfDay(),
        };

        $endDate = Carbon::now()->endOfDay();

        // 4. Doing grouping for PostgreSQL with division into likes and dislikes through the conditional aggregate COUNT(CASE...)
        if ($this->filter === 'year') {
            $reactionsData = Like::query()
                ->select([
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as period"),
                    DB::raw("COUNT(CASE WHEN is_like = true THEN 1 END) as likes_count"),
                    DB::raw("COUNT(CASE WHEN is_like = false THEN 1 END) as dislikes_count")
                ])
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->get()
                ->keyBy('period');
        } else {
            $reactionsData = Like::query()
                ->select([
                    DB::raw("DATE(created_at) as period"),
                    DB::raw("COUNT(CASE WHEN is_like = true THEN 1 END) as likes_count"),
                    DB::raw("COUNT(CASE WHEN is_like = false THEN 1 END) as dislikes_count")
                ])
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw("DATE(created_at)"))
                ->get()
                ->keyBy('period');
        }

        // 5. Form a grid of points on the graph without missing data
        $labels = [];
        $likesDataset = [];
        $dislikesDataset = [];

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $periodString = ($this->filter === 'year')
                ? $currentDate->format('Y-m')
                : $currentDate->format('Y-m-d');

            // Adding a text signature
            $labels[] = ($this->filter === 'year')
                ? $currentDate->translatedFormat('F Y')
                : $currentDate->translatedFormat('d M');

            // Extracting data for the current date from the collection (or setting 0)
            $record = $reactionsData->get($periodString);

            $likesDataset[] = $record ? $record->likes_count : 0;
            $dislikesDataset[] = $record ? $record->dislikes_count : 0;

            // The iteration step
            $this->filter === 'year' ? $currentDate->addMonth() : $currentDate->addDay();
        }

        return [
            // Passing two sets of Chart data.js — each with its own color
            'datasets' => [
                [
                    'label' => 'Likes',
                    'data' => $likesDataset,
                    'borderColor' => '#10b981', // Green (emerald-500)
                    'backgroundColor' => 'rgba(16, 185, 129, 0.05)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Dislikes',
                    'data' => $dislikesDataset,
                    'borderColor' => '#ef4444', // Red (red-500)
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
        /*
        return [
            //
        ];
        */
    }

    protected function getType(): string
    {
        return 'line';
    }
}
