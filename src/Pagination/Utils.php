<?php

declare(strict_types=1);

namespace Sys\Pagination;

trait Utils
{
    private function getPaginationData(int $count_pages, int $page)
    {
        $source5 = $this->getSource5($count_pages, $page);
        $source7 = $this->getSource7($count_pages, $page);
        $data['pagination7'] = $this->getDataFromSource($source7);
        $data['pagination5'] = $this->getDataFromSource($source5);

        foreach ($this->perPages as $item) {
            $data['per_pages'][] = [
                'label' => (int) $item,
                'link' => $this->helper->replaceQueryParams(['page' => '1', 'limit' => $item]),
                'isActive' => $item === $this->limit,
            ];
        }

        return $data;
    }

    private function getDataFromSource(array $source)
    {
        $filter = array_values(array_filter($source, fn($val) => $val[0]));

        return array_map(function ($val) {
            if ($val[0]) {
                return [
                    'label' => $val[3] ?? (string) $val[1],
                    'link' => $this->helper->replaceQueryParams(['page' => $val[1], 'limit' => $this->limit]),
                    'isActive' => $val[2],
                ];
            }
        }, $filter);
    }

    private function getSource5(int $count_pages, int $page)
    {
        if ($count_pages === 0) {
            return [];
        } elseif ($count_pages <= 5) {
            for ($i = 1; $i <= $count_pages && $i <= 5; ++$i) {
                $res[] = [true, $i, $i == $page];
            }
        } else {
            $res = [
                [$page > 1, 1, false],
                [$count_pages === $page, $page - 3, false],
                [($count_pages - $page) < 2, $page - 2, false],
                [$page > 2, $page - 1, false],
                [true, $page, true],
                [($count_pages - $page) >= 2, $page + 1, false],
                [$page < 3, $page + 2, false],
                [$page === 1, $page + 3, false],
                [($count_pages - $page) >= 1, $count_pages, false],
            ];
        }

        return $res;
    }

    private function getSource7(int $count_pages, int $page)
    {
        if ($count_pages === 0) {
            return [];
        } elseif ($count_pages <= 7) {
            for ($i = 1; $i <= $count_pages && $i <= 7; ++$i) {
                $res[] = [true, $i, $i == $page];
            }
        } else {
            $res = [
                [$page > 1, 1, false],
                [
                    $page > 2,
                    $this->ellipsisLeft($count_pages, $page),
                    false,
                    $page < 5 ? 2 : '...'
                ],
                [($count_pages - $page) < 1, $page - 4, false],
                [($count_pages - $page) < 2, $page - 3, false],
                [($count_pages - $page) < 3, $page - 2, false],
                [($page - 1) > 2, $page - 1, false],
                [true, $page, true],
                [($page + 1) < $count_pages, $page + 1, false],
                [$page < 4 && ($page + 2) < $count_pages, $page + 2, false],
                [$page < 3 && ($page + 3) < $count_pages, $page + 3, false],
                [$page < 2 && ($page + 4) < $count_pages, $page + 4, false],
                [
                    ($count_pages - $page) >= 3 && ($page + 2) > 1,
                    $this->ellipsisRight($page, $count_pages),
                    false,
                    ($count_pages - $page) < 4 ? $count_pages - 1 : '...',
                ],
                [($count_pages - $page) >= 1, $count_pages, false],
            ];
        }

        return $res;
    }

    private function ellipsisLeft($page, $count_pages)
    {
        $max = match ($count_pages - $page) {
            0 => $count_pages - 4,
            1 => $count_pages - 3,
            2 => $count_pages - 2,
            default => $count_pages - 1,
        };

        return (int) ceil((1 + $max) / 2);
    }

    private function ellipsisRight($page, $count_pages)
    {
        $min = $page < 5 ? 5 : $page + 1;

        return (int) floor(($min + $count_pages) / 2);
    }
}
