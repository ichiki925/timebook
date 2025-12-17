<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LessonSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 今日の予約一覧を取得
     * GET /api/dashboard/today-reservations
     */
    public function getTodayReservations(Request $request)
    {
        $teacherId = $request->user()->id;
        $today = Carbon::today();

        // 今日の予約を取得（lesson_slotsを通じて）
        $reservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId, $today) {
            $query->where('teacher_id', $teacherId)
                ->whereDate('date', $today);
        })
        ->with('lessonSlot')
        ->get()
        ->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'student_name' => $reservation->student_name,
                'student_email' => $reservation->student_email,
                'lesson_start_time' => $reservation->lessonSlot->start_time,
                'lesson_end_time' => $reservation->lessonSlot->end_time,
                'lesson_slot' => [
                    'duration_minutes' => $reservation->lessonSlot->duration,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today->format('Y-m-d'),
                'count' => $reservations->count(),
                'reservations' => $reservations,
            ],
        ]);


    }

    /**
     * 今週の予約一覧を取得
     * GET /api/dashboard/week-reservations
     */
    public function getWeekReservations(Request $request)
    {
        $teacherId = $request->user()->id;

        // 今週の月曜日と日曜日を取得
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 今週の予約を取得
        $reservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId, $startOfWeek, $endOfWeek) {
            $query->where('teacher_id', $teacherId)
                ->whereBetween('date', [$startOfWeek, $endOfWeek]);
        })
        ->with('lessonSlot')
        ->get();

        // 日付でグループ化
        $groupedReservations = $reservations->groupBy(function ($reservation) {
            return Carbon::parse($reservation->lessonSlot->date)->format('Y-m-d');
        });

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startOfWeek->format('Y-m-d'),
                'end_date' => $endOfWeek->format('Y-m-d'),
                'total_count' => $reservations->count(),
                'reservations_by_date' => $groupedReservations,
            ],
        ]);

    }

    /**
     * 統計情報を取得
     * GET /api/dashboard/stats
     */
    public function getStats(Request $request)
    {
        $teacherId = $request->user()->id;

        // 総予約数
        $totalReservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })->count();


        // 今月の予約数（キャンセル含まない）
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $thisMonthReservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId, $startOfMonth, $endOfMonth) {
            $query->where('teacher_id', $teacherId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth]);
        })
        ->where('status', '!=', 'canceled')
        ->count();

        // キャンセル数
        $canceledReservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->where('status', 'canceled')
        ->count();

        $cancelRate = $totalReservations > 0
            ? round(($canceledReservations / $totalReservations) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_reservations' => $totalReservations,
                'this_month_reservations' => $thisMonthReservations,
                'canceled_reservations' => $canceledReservations,
                'cancel_rate' => $cancelRate,
            ],
        ]);


    }

    /**
     * 直近の予約を取得
     * GET /api/dashboard/next-reservation
     */
    public function getNextReservation(Request $request)
    {
        $teacherId = $request->user()->id;
        $now = Carbon::now();

        // 現在時刻以降の最も近い予約を取得
        $nextReservation = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId, $now) {
            $query->where('teacher_id', $teacherId)
                ->where(function ($q) use ($now) {
                    $q->where('date', '>', $now->format('Y-m-d'))
                        ->orWhere(function ($q2) use ($now) {
                            $q2->where('date', $now->format('Y-m-d'))
                            ->where('start_time', '>', $now->format('H:i:s'));
                        });
                });
        })
        ->where('status', '!=', 'canceled')
        ->with('lessonSlot')
        ->orderBy(function ($query) {
            return $query->from('lesson_slots')
                        ->whereColumn('lesson_slots.id', 'reservations.lesson_slot_id')
                        ->select('date');
        })
        ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'next_reservation' => $nextReservation,
            ],
        ]);
    }

    /**
     * 月間カレンダー表示用データを取得
     * GET /api/dashboard/month-reservations?year=2025&month=12
     */
    public function getMonthReservations(Request $request)
    {
        $teacherId = $request->user()->id;

        // 年月を取得（指定がなければ今月）
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        // 指定月の予約を取得
        $reservations = Reservation::whereHas('lessonSlot', function ($query) use ($teacherId, $startOfMonth, $endOfMonth) {
            $query->where('teacher_id', $teacherId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth]);
        })
        ->with('lessonSlot')
        ->get();

        // 日付ごとの予約数を集計
        $reservationsByDate = $reservations->groupBy(function ($reservation) {
            return Carbon::parse($reservation->lessonSlot->date)->format('Y-m-d');
        })->map(function ($dateReservations) {
            return [
                'count' => $dateReservations->count(),
                'canceled_count' => $dateReservations->where('status', 'canceled')->count(),
                'reservations' => $dateReservations,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startOfMonth->format('Y-m-d'),
                'end_date' => $endOfMonth->format('Y-m-d'),
                'total_count' => $reservations->count(),
                'reservations_by_date' => $reservationsByDate,
            ],
        ]);
    }
}