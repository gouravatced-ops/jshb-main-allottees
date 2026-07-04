<?php

namespace App\Services;

use App\Models\Allottee;
use App\Models\AllotteeProcessStep;
use Illuminate\Support\Facades\Auth;

class ProcessStepService
{
    public function processStepBlueprint(): array
    {
        return [
            // OVERVIEW
            [
                'order_key'   => 1,
                'menu_key'    => 'quick-overview',
                'title'       => 'Overview',
                'description' => 'Quick Overview',
                'icon'        => 'fa-solid fa-gauge-high',
                'blade'       => 'overview',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time', 'null'],
                ],
            ],

            // ALLOTTEE DETAILS
            [
                'order_key'   => 2,
                'menu_key'    => 'allottee-details',
                'title'       => 'Allottee Details',
                'description' => 'Allottee Details',
                'icon'        => 'fa-solid fa-user',
                'blade'       => 'allottee-details',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time', 'null'],
                ],
            ],

            // ALLOTTEE DOCUMENT
            [
                'order_key'   => 3,
                'menu_key'    => 'letter-order-issued',
                'title'       => 'Letter/Orders Issued',
                'description' => 'Letter/Orders Issued',
                'icon'        => 'fa-solid fa-file-signature',
                'blade'       => 'letter-order-issued',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time', 'null'],
                ],
            ],

            // LOTTERY
            [
                'order_key'   => 4,
                'menu_key'    => 'lottery',
                'title'       => 'Lottery',
                'description' => 'Lottery related activities',
                'icon'        => 'fa-solid fa-ticket',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time', 'null'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'payment-details',
                        'title'        => 'Lottery Payment',
                        'icon'         => 'fa-solid fa-money-check-dollar',
                        'blade'        => 'payment-details',
                    ],
                ],
            ],

            // ALLOTMENT
            [
                'order_key'   => 5,
                'menu_key'    => 'allotment',
                'title'       => 'Allotment',
                'description' => 'Allotment related activities',
                'icon'        => 'fa-solid fa-file-signature',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time', 'null'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'generate-allotment',
                        'title'        => 'Allotment Letter',
                        'icon'         => 'fa-solid fa-file-lines',
                        'blade'        => 'allotment-letter',
                    ],
                    [
                        'order_key'    => 2,
                        'sub_menu_key' => 'allotment-demand-note',
                        'title'        => '15% Demand Note',
                        'icon'         => 'fa-solid fa-file-invoice-dollar',
                        'blade'        => 'initial-payment',
                    ],
                    [
                        'order_key'    => 3,
                        'sub_menu_key' => 'allotment-possession-letter',
                        'title'        => 'Possession Letter',
                        'icon'         => 'fa-solid fa-key',
                        'blade'        => 'allotment-possession-letter',
                    ],
                    [
                        'order_key'    => 4,
                        'sub_menu_key' => 'agreement-document-letter',
                        'title'        => 'Agreement',
                        'icon'         => 'fa-solid fa-key',
                        'blade'        => 'allotment-agreement-letter',
                    ],
                ],
            ],

            // CHOOSE PAYMENT OPTION
            [
                'order_key'   => 6,
                'menu_key'    => 'choose-payment-option',
                'title'       => 'Choose Payment Option',
                'description' => 'Choose EMI or One Time Payment',
                'icon'        => 'fa-solid fa-wallet',
                'blade'       => 'choose-payment-option',
                'always_show' => false,
                'visible_if'  => [
                    'payment_option' => ['null'],
                ],
            ],

            // PROPERTY PAYMENT
            [
                'order_key'   => 7,
                'menu_key'    => 'property-payment',
                'title'       => 'Property Payment',
                'description' => 'One time property payment',
                'icon'        => 'fa-solid fa-building-circle-check',
                'always_show' => false,
                'visible_if'  => [
                    'payment_option' => ['one_time'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'one-time-payment',
                        'title'        => 'One Time Payment',
                        'icon'         => 'fa-solid fa-indian-rupee-sign',
                        'blade'        => 'one-time-payment',
                    ],
                    [
                        'order_key'    => 2,
                        'sub_menu_key' => 'payment-history',
                        'title'        => 'Payment History',
                        'icon'         => 'fa-solid fa-clock-rotate-left',
                        'blade'        => 'payment-history',
                    ],
                ],
            ],

            // EMI MANAGEMENT
            [
                'order_key'   => 8,
                'menu_key'    => 'emi-management',
                'title'       => 'EMI Management',
                'description' => 'EMI Management',
                'icon'        => 'fa-solid fa-calendar-days',
                'always_show' => false,
                'visible_if'  => [
                    'payment_option' => ['emi'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'emi-dashboard',
                        'title'        => 'EMI Dashboard',
                        'icon'         => 'fa-solid fa-chart-line',
                        'blade'        => 'emi-dashboard',
                    ],
                    [
                        'order_key'    => 2,
                        'sub_menu_key' => 'monthly-emi',
                        'title'        => 'Pay EMI',
                        'icon'         => 'fa-solid fa-credit-card',
                        'blade'        => 'monthly-emi',
                    ],
                    [
                        'order_key'    => 3,
                        'sub_menu_key' => 'emi-schedule',
                        'title'        => 'EMI Schedule',
                        'icon'         => 'fa-solid fa-calendar-check',
                        'blade'        => 'emi-schedule',
                    ],
                    [
                        'order_key'    => 4,
                        'sub_menu_key' => 'emi-history',
                        'title'        => 'EMI History',
                        'icon'         => 'fa-solid fa-receipt',
                        'blade'        => 'emi-history',
                    ],
                ],
            ],

            // NOC
            [
                'order_key'   => 9,
                'menu_key'    => 'noc',
                'title'       => 'NOC',
                'description' => 'NOC related process',
                'icon'        => 'fa-solid fa-file-circle-check',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'site-verification',
                        'title'        => 'Site Verification',
                        'icon'         => 'fa-solid fa-location-dot',
                        'blade'        => 'site-verification',
                    ],
                    [
                        'order_key'    => 2,
                        'sub_menu_key' => 'extra-construction-calculation',
                        'title'        => 'Extra Construction',
                        'icon'         => 'fa-solid fa-ruler-combined',
                        'blade'        => 'extra-construction-calculation',
                    ],
                    [
                        'order_key'    => 3,
                        'sub_menu_key' => 'generate-noc',
                        'title'        => 'Generate NOC',
                        'icon'         => 'fa-solid fa-file-circle-plus',
                        'blade'        => 'generate-noc',
                    ],
                ],
            ],

            // FINAL CALCULATION
            [
                'order_key'   => 10,
                'menu_key'    => 'final-calculation',
                'title'       => 'Final Calculation',
                'description' => 'Final calculation process',
                'icon'        => 'fa-solid fa-calculator',
                'blade'       => 'final-calculation',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi'],
                ],
            ],

            // REGISTRY
            [
                'order_key'   => 11,
                'menu_key'    => 'registry',
                'title'       => 'Registry',
                'description' => 'Registry related process',
                'icon'        => 'fa-solid fa-file-contract',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time'],
                ],
                'submenus'    => [
                    [
                        'order_key'    => 1,
                        'sub_menu_key' => 'request-for-documentation',
                        'title'        => 'Documentation Request',
                        'icon'         => 'fa-solid fa-folder-open',
                        'blade'        => 'request-for-documentation',
                    ],
                    [
                        'order_key'    => 2,
                        'sub_menu_key' => 'upload-registry-deed',
                        'title'        => 'Upload Registry Deed',
                        'icon'         => 'fa-solid fa-upload',
                        'blade'        => 'upload-registry-deed',
                    ],
                    [
                        'order_key'    => 3,
                        'sub_menu_key' => 'registry-generate-noc',
                        'title'        => 'Generate Registry NOC',
                        'icon'         => 'fa-solid fa-file-shield',
                        'blade'        => 'registry-generate-noc',
                    ],
                ],
            ],

            // NAME TRANSFER
            [
                'order_key'   => 12,
                'menu_key'    => 'name-transfer',
                'title'       => 'Name Transfer',
                'description' => 'Name transfer process',
                'icon'        => 'fa-solid fa-user-pen',
                'blade'       => 'name-transfer',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time'],
                ],
            ],

            // LEASE FREE HOLD
            [
                'order_key'   => 13,
                'menu_key'    => 'lease-free-hold',
                'title'       => 'Lease Free Hold',
                'description' => 'Lease free hold process',
                'icon'        => 'fa-solid fa-building-shield',
                'blade'       => 'lease-free-hold',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['emi', 'one_time'],
                ],
            ],

            // ALLOTMENT CANCELLATION
            [
                'order_key'   => 14,
                'menu_key'    => 'allotment-cancellation',
                'title'       => 'Allotment Cancellation',
                'description' => 'Allotment cancellation process',
                'icon'        => 'fa-solid fa-ban',
                'blade'       => 'allotment-cancellation',
                'always_show' => true,
                'visible_if'  => [
                    'payment_option' => ['null'],
                ],
            ],
        ];
    }

    public function ensureProcessSteps(Allottee $allottee): void
    {
        if (AllotteeProcessStep::where('allottee_id', $allottee->id)->exists()) {
            return;
        }

        $now    = now();
        $userId = Auth::id() ?? 1;

        $rows   = [];
        $stepNo = 1;

        foreach ($this->processStepBlueprint() as $menu) {
            $submenus = $menu['submenus'] ?? [[
                'sub_menu_key' => null,
                'title'        => $menu['title'],
                'blade'        => $menu['blade'] ?? null,
                'icon'         => $menu['icon'] ?? null,
            ]];

            foreach ($submenus as $index => $submenu) {
                $rows[] = [
                    'allottee_id'   => $allottee->id,
                    'menu_order'    => $menu['order_key'],
                    'step_order'    => $index + 1,
                    'step_no'       => $stepNo,
                    'menu_key'      => $menu['menu_key'],
                    'sub_menu_key'  => $submenu['sub_menu_key'],
                    'route_name'    => 'allottee.process.' . ($submenu['sub_menu_key'] ?? $menu['menu_key']),
                    'process_group' => $menu['menu_key'],
                    'icons'         => $submenu['icon'] ?? $menu['icon'] ?? 'fa-solid fa-circle',
                    'title'         => $submenu['title'],
                    'description'   => $menu['description'] ?? null,
                    'blade'         => $submenu['blade'] ?? null,
                    'status'        => $this->resolveStepStatus($menu['order_key'], $index),
                    'is_active'     => true,
                    'started_at'    => $now,
                    'meta'          => json_encode([
                        'always_show' => $menu['always_show'] ?? false,
                        'visible_if'  => $menu['visible_if'] ?? [],
                    ], JSON_UNESCAPED_UNICODE),
                    'created_by'    => $userId,
                    'updated_by'    => $userId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $stepNo++;
            }
        }

        AllotteeProcessStep::upsert(
            $rows,
            ['allottee_id', 'menu_key', 'sub_menu_key'],
            [
                'menu_order', 'step_order', 'route_name', 'process_group', 'icons', 'step_no', 'title',
                'description', 'blade', 'status', 'is_active', 'meta', 'updated_by', 'updated_at'
            ]
        );
    }

    private function resolveStepStatus(int $menuOrder, int $subMenuIndex = 0): string
    {
        if (in_array($menuOrder, [1, 2, 3, 4])) {
            return AllotteeProcessStep::STATUS_COMPLETED ?? 'completed';
        }
        if ($menuOrder === 4) {
            return match ($subMenuIndex) {
                0       => AllotteeProcessStep::STATUS_COMPLETED ?? 'completed',
                1       => AllotteeProcessStep::STATUS_PENDING ?? 'pending',
                default => AllotteeProcessStep::STATUS_LOCKED ?? 'locked',
            };
        }
        if ($menuOrder === 5) {
            return AllotteeProcessStep::STATUS_PENDING ?? 'pending';
        }
        return AllotteeProcessStep::STATUS_LOCKED ?? 'locked';
    }
}
