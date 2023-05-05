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

    $seats = Seat::get();
    $sameRow = $seats->where('row_name', $selectedRow)->all();

    $availableSeats = [];
    $ids = [];
    foreach ($sameRow as $row) {
        $dbSeatNo = $row->row_name . $row->column_no;
        if (count($availableSeats) == $totalSeats) break;
        else if ($seatNo == $dbSeatNo && $row->status == 1) {
            $seatNo = $row->row_name . ($selectedColumn == 10 ? 1 : $selectedColumn+1);
            session()->put('flag', 1);
            bookSeats($seatNo, $totalSeats);
        } else if ($row->status == 0) {
            if (($seatNo == $dbSeatNo || count($availableSeats)) && count($availableSeats) < $totalSeats) {
                array_push($availableSeats, $dbSeatNo);
                array_push($ids, $row->id);
            }
        } else {
            if (count($availableSeats) && count($availableSeats) < $totalSeats /* && $row->column_no == ($selectedColumn - 1) */) {
                $seatNo = $row->row_name == 'T' ? 'A1' : (++$row->row_name) . 1;
                session()->put('flag', 1);
                bookSeats($seatNo, $totalSeats);
            }
        }
    }
    if (session('flag') == 1) {
        session()->forget('flag');
        return back()->withError('Your selected seats are booked. Suggested seats are ' . implode(', ', $availableSeats));
    } else {
        session()->forget('flag');
        DB::table('seats')->whereIn('id', $ids)->update(['status' => 1]);
        return back()->withSuccess('Booked seats are ' . implode(', ', $availableSeats));
    }
}
