<?php

namespace App\Http\Controllers;

use App\Ticket;
use App\Http\Controllers\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;

class TicketController extends Controller
{
    public function index()
    {
        return view("ticket.new_ticket");
    }

    public function findIndex()
    {
        return view("ticket.find_ticket");
    }

    public function listIndex()
    {
        $tickets = Ticket::all();
        $ticketCount = $tickets->count();
        return view("admin.tickets.index")->with(compact('tickets','ticketCount'));
    }

    public function find()
    {
        return view("ticket.find_ticket");
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'nim' => 'required|numeric',
            'class' => 'required',
            'detail' => 'required',
            'feedback' => 'required',
        ]);


        $nim = $request->nim;

        $ticket_id = "RAZ$nim" . "KY" . $this->generateRandomString(8);


        $image = $request->file('file');
        $counter = 0;

        $imageArr = array();

        if ($image != "") {
            foreach ($image as $file) {
                $filename = time() . "-" . $counter . "." . $file->getClientOriginalExtension();
                $file->move(public_path("ticket/$nim"), $filename);
                array_push($imageArr, $filename);
                $counter++;
            }
        }

        $ticket = Ticket::create([
            'name' => $request->name,
            'nid' => $request->nim,
            'class' => $request->class,
            'faculty' => $request->faculty,
            'account_type' => $request->account_type,
            'ticket_type' => $request->ticket_type,
            'status' => 0,
            'ticket_id' => $ticket_id,
            'ticket_detail' => $request->detail,
            'answers_pref' => $request->feedback,
            'answers_ticket' => "",
            'file' => implode(",", $imageArr)
        ]);
        $insertedId = $ticket->id;
        $ticketID = $ticket_id;

        if ($ticket) {
            return redirect("ticket/find/$ticketID")->with("ticketID");
        } else {
            return "failed";
        }
    }

    public function checkTicket(Request $request, $ticketNum)
    {
        $ticket = Ticket::where('ticket_id', $ticketNum)
            ->orderBy('name')
            ->first();

        if ($ticket == null) {
            return back()->withErrors(["Tiket Tidak Ditemukan"]);
        }

        $accountType = $ticket->account_type;
        $accountDesc = $this->getAccountType($accountType);
        return view("ticket.check_ticket")->with(
            compact('ticketNum', 'ticket', 'accountDesc')
        );
    }

    function generateRandomString($length = 10)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getAccountType(String $accountType){
        $accountDesc = "Unknown";
        switch ($accountType) {
            case 1:
                $accountDesc = "Asisten/Kakak Mentor";
                break;
            case 2:
                $accountDesc = "Mahasiswa";
                break;
            case 3:
                $accountDesc = "Dosen";
                break;

            default:
                $accountDesc = "Unknown";
                break;
        }
        return $accountDesc;
    }
}
