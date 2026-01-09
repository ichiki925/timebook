<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LessonSlot;
use App\Mail\StudentReservationConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class StudentReservationController extends Controller
{
    /**
     * 予約作成（会員情報を自動入力）
     */
    public function store(Request $request)
    {
        // バリデーション
        $validated = $request->validate([
            'lesson_slot_id' => 'required|exists:lesson_slots,id',
        ], [
            'lesson_slot_id.required' => 'レッスン枠を選択してください',
            'lesson_slot_id.exists' => '選択されたレッスン枠が見つかりません',
        ]);

        // ログインユーザー情報を取得
        $user = $request->user();

        // レッスン枠の取得
        $lessonSlot = LessonSlot::findOrFail($validated['lesson_slot_id']);

        // レッスン枠が既に予約済みでないか確認
        if ($lessonSlot->is_reserved) {
            return response()->json([
                'message' => 'このレッスン枠は既に予約されています'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 予約作成（会員情報を自動入力）
            $reservation = Reservation::create([
                'lesson_slot_id' => $lessonSlot->id,
                'user_id' => $user->id,
                'student_name' => $user->name,
                'student_email' => $user->email,
                'student_phone' => $request->input('student_phone'),
                'course_type' => $request->input('course_type'),
                'notes' => $request->input('notes'),  // 備考（任意）
            ]);

            // レッスン枠を予約済みにする
            $lessonSlot->is_reserved = true;
            $lessonSlot->save();

            // 確認メールを送信
            Mail::to($user->email)->send(new StudentReservationConfirmed($reservation, $lessonSlot));

            DB::commit();

            return response()->json([
                'message' => '予約が完了しました。確認メールをお送りしました。',
                'reservation' => [
                    'id' => $reservation->id,
                    'lesson_date' => $lessonSlot->lesson_date,
                    'start_time' => $lessonSlot->start_time,
                    'end_time' => $lessonSlot->end_time,
                    'student_name' => $reservation->student_name,
                    'student_email' => $reservation->student_email,
                    'notes' => $reservation->notes,
                    'created_at' => $reservation->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => '予約の作成に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 予約履歴表示（ログインユーザーの予約のみ）
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // ログインユーザーの予約を取得（新しい順）
        $reservations = Reservation::with('lessonSlot')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'lesson_date' => $reservation->lessonSlot->lesson_date,
                    'start_time' => $reservation->lessonSlot->start_time,
                    'end_time' => $reservation->lessonSlot->end_time,
                    'student_name' => $reservation->student_name,
                    'student_email' => $reservation->student_email,
                    'notes' => $reservation->notes,
                    'created_at' => $reservation->created_at,
                ];
            });

        return response()->json([
            'reservations' => $reservations
        ]);
    }

    /**
     * 予約詳細表示
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        // 予約を取得
        $reservation = Reservation::with('lessonSlot')->findOrFail($id);

        // 自分の予約でない場合はエラー
        if ($reservation->user_id !== $user->id) {
            return response()->json([
                'message' => 'この予約情報にアクセスする権限がありません'
            ], 403);
        }

        return response()->json([
            'reservation' => [
                'id' => $reservation->id,
                'lesson_date' => $reservation->lessonSlot->lesson_date,
                'start_time' => $reservation->lessonSlot->start_time,
                'end_time' => $reservation->lessonSlot->end_time,
                'student_name' => $reservation->student_name,
                'student_email' => $reservation->student_email,
                'notes' => $reservation->notes,
                'created_at' => $reservation->created_at,
            ]
        ]);
    }

    /**
     * 予約キャンセル（削除）
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        // 予約を取得
        $reservation = Reservation::with('lessonSlot')->findOrFail($id);

        // 自分の予約でない場合はエラー
        if ($reservation->user_id !== $user->id) {
            return response()->json([
                'message' => 'この予約をキャンセルする権限がありません'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // レッスン枠の予約状態を解除
            $lessonSlot = $reservation->lessonSlot;
            $lessonSlot->is_reserved = false;
            $lessonSlot->save();

            // 予約を削除
            $reservation->delete();

            DB::commit();

            return response()->json([
                'message' => '予約をキャンセルしました'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => '予約のキャンセルに失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}