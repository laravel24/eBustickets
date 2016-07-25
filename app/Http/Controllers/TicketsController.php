<?php

namespace App\Http\Controllers;

use App\Model\Booking;
use App\Model\Route;
use App\Model\Routespoint;
use App\Model\Seatplan;
use App\Model\Ticket;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tickets = Booking::with(['init','ends','route','buses','user'])
                        ->whereCompaniesId(Sentinel::getUser()->companies_id)
                        ->get();
        return view('tickets.index',compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function booking()
    {
        $pays = DB::table('payments')->lists('name','id');
        $routes = Route::whereCompaniesId(Sentinel::getUser()->companies_id)->get();
        $buses = DB::table('buses')
                    ->whereCompaniesId(Sentinel::getUser()->companies_id)
                    ->lists('bus_number','id');
        $bookings = Booking::with(['init','ends','route','buses','user'])
                    ->whereCompaniesId(Sentinel::getUser()->companies_id)
                    ->get();
        return view('tickets.booking',compact('routes','buses','bookings','pays'));
    }


    /*
     *  AJAX ROUTINGS
     * */
    public function booking_post()
    {
        $input = [
            'firstname' => Input::get('first_name'),
            'lastname' => Input::get('last_name'),
            'phonenumber' => Input::get('phone_number'),
            'routes_id' => Input::get('routes'),
            'initial' => Input::get('location'),
            'final' => Input::get('destination'),
            'buses_id' => Input::get('buses'),
            'seat_number' => Input::get('seatno'),
            'payments_id' => Input::get('payments'),
            'amount' => str_replace(",","",Input::get('amount')),
            'dateoftravel' => Input::get('travelday')
        ];
        $rules = [
            'routes_id' => 'required',
            'initial' => 'required',
            'final' => 'required',
            'buses_id' => 'required',
            'seat_number' => 'required',
            'dateoftravel' => 'required'
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return Redirect::to('tickets/bookings')
                ->withErrors($validator)
                ->withInput();
        }else{
//            return $input;
            Booking::FirstOrCreate($input);
            Session::flash('success','Successful Added');
            return Redirect::to('tickets/bookings');
        }

    }

    public function routes_taking()
    {
        $id = Input::get('routes_id');
        $routes = Routespoint::whereRoutesId($id)->get();
        $output = "<option value=''>--Select--</option>";
        foreach($routes as $route){
            $output .= "<option value='".$route->id."'>".$route->name."</option>";
        }
        return $output;
    }

    public function routes_location()
    {
        $id = Input::get('data');
        $location = Input::get('location');
        $routes = Routespoint::whereRoutesId($id)->whereNotIn('Id',[$location])->get();
        $output = "<option value=''>--Select--</option>";
        foreach($routes as $route){
            $output .= "<option value='".$route->id."'>".$route->name."</option>";
        }
        return $output;
    }

    /**
     *   TICKETS TEMPLATES
     */
    public function templates()
    {
        $buses = DB::table('buses')
            ->whereCompaniesId(Sentinel::getUser()->companies_id)
            ->lists('bus_number','id');
        return view('tickets.seating-plan',compact('buses','left','right','seat'));
    }
    public function template_view()
    {
        $id = Input::get('buses');
        $seat = Seatplan::whereBusesId($id)->first();
        $right = range($seat->firstletter, $seat->lastletter);
        $view = view('tickets.ticket-template',compact('right','seat'));
        return $view;

    }
}