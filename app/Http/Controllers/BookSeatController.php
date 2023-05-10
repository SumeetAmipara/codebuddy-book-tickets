<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Seat;

class BookSeatController extends Controller
{
    public function view()
    {
        return view('book-seats');
    }

    public function seats(Request $request)
    {
        $request->validate([
            'seat-name' => ['required', 'regex:/^[A-Z]{1}([1-9]|10){1}$/'],
            'total-seats' => ['required', 'integer', 'min:1', 'max:5']
        ]);

        $emptySeats = Seat::select('id')->where('status', 0)->count();

        if ($emptySeats < $request['total-seats']) {
            return back()->withError('Only ' . $emptySeats . ' seats are available...!');
        }

        session()->put('recursion', 1);

        return bookSeats($request['seat-name'], $request['total-seats']);
    }

    public function resetSeats()
    {
        DB::table('seats')->update([
            'status' => 0
        ]);

        return 'All seats are now available';
    }
}

function bookSeats($seatNo, $totalSeats)
{
    $selectedRow = preg_replace('/[^a-zA-Z]/', '', $seatNo);
    $selectedColumn = intval(preg_replace('/[^0-9]/', '', $seatNo));

    $case1 = Seat::select(DB::raw('COUNT(id) as total, GROUP_CONCAT(CONCAT(row_name, column_no) SEPARATOR ", ") as booked_seats'))
        ->where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn, $totalSeats) {
            $q->where('column_no', '>=', $selectedColumn)
            ->where('column_no', '<', ($selectedColumn + $totalSeats));
        })
        ->where('status', 0)
        ->first();

    if ($case1->total == $totalSeats) {
        if (session('flag') == 1) {
            forgetSession('recursion');
            forgetSession('flag');
            return back()->withError('Your selected seats are booked. Available suggested seats are ' . $case1->booked_seats);
        }
        Seat::where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn, $totalSeats) {
            $q->where('column_no', '>=', $selectedColumn)
            ->where('column_no', '<', ($selectedColumn + $totalSeats));
        })
        ->where('status', 0)
        ->update([ 'status' => 1]);
        forgetSession('recursion');
        return back()->withSuccess('Booked seats are ' . $case1->booked_seats);
    }

    $case2 = Seat::select(DB::raw('COUNT(id) as total, GROUP_CONCAT(CONCAT(row_name, column_no) SEPARATOR ", ") as booked_seats'))
        ->where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn, $totalSeats) {
            $q->where('column_no', '<=', $selectedColumn)
            ->where('column_no', '>', ($selectedColumn - $totalSeats));
        })
        ->where('status', 0)
        ->first();

    if ($case2->total == $totalSeats) {
        Seat::where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn, $totalSeats) {
            $q->where('column_no', '<=', $selectedColumn)
            ->where('column_no', '>', ($selectedColumn - $totalSeats));
        })
        ->where('status', 0)
        ->update([ 'status' => 1]);
        forgetSession('recursion');
        return back()->withSuccess('Booked seats are ' . $case2->booked_seats);
    }

    $case3 = Seat::select('id', DB::raw('CONCAT(row_name, column_no) as seat_no'), 'column_no')
        ->where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn) {
            $q->where('column_no', '>', $selectedColumn)
            ->where('column_no', '<=', 10);
        })
        ->where('status', 0)
        ->limit($totalSeats)
        ->get();

    $case4 = Seat::select('id', DB::raw('CONCAT(row_name, column_no) as seat_no'), 'column_no')
        ->where('row_name', $selectedRow)
        ->where(function ($q) use ($selectedColumn) {
            $q->where('column_no', '>=', 1)
            ->where('column_no', '<', $selectedColumn);
        })
        ->where('status', 0)
        ->limit($totalSeats)
        ->get();

    if (count($case3) >= $totalSeats && count($case4) >= $totalSeats) {
        forgetSession('recursion');
        if (abs($case3[0]->column_no - $selectedColumn) >= abs($case4[0]->column_no - $selectedColumn)) {
            $aSeats = $case3->pluck('seat_no')->all();
            return back()->withError('Your selected seats are booked. Available suggested seats are ' . implode(', ', $aSeats));
        } else {
            $aSeats = $case4->pluck('seat_no')->all();
            return back()->withError('Your selected seats are booked. Available suggested seats are ' . implode(', ', $aSeats));
        }
    } else if (count($case3) >= $totalSeats) {
        forgetSession('recursion');
        $aSeats = $case3->pluck('seat_no')->all();
        return back()->withError('Your selected seats are booked. Available suggested seats are ' . implode(', ', $aSeats));
    } else {
        forgetSession('recursion');
        $aSeats = $case4->pluck('seat_no')->all();
        return back()->withError('Your selected seats are booked. Available suggested seats are ' . implode(', ', $aSeats));
    }

    if (session('recursion') <= 20) {
        session()->increment('recursion');
        session()->put('flag', 1);
        $newSeatNo = $selectedRow == 'T' ? 'A' . $selectedColumn : (++$selectedRow) . $selectedColumn;
        return bookSeats($newSeatNo, $totalSeats);
    } else {
        forgetSession('recursion');
        return back()->withError($totalSeats . ' consecutive seats are not available...!');
    }
}

function forgetSession($var)
{
    session()->forget($var);
}
