<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\Admin\TextMessageMail;
use App\Mail\Admin\ContactMail;
use App\Mail\Admin\CalculateMail;
use Illuminate\Support\Facades\Validator;
use App\Services\GoogleSheetService;

class LeadController extends Controller
{
    protected $googleSheetsService;

    public function __construct(GoogleSheetService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    public function index(Request $request): JsonResponse
    {
        try{
            $perPage = $request->query('per_page', 10);
            $query = Lead::orderBy('id', 'desc');
            $leads = $query->paginate($perPage);
            if (empty($leads)) throw new Exception('No data found', 200);
            return response()->json($leads,200);
        } catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make(
                $request->all(),
                [
                    'lead_area' => 'required',
                ],
                [
                    'lead_area.required' => 'lead_area required.',
                ]
            );
            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            $lead = Lead::create($request->all());
            if($request->lead_area==0){
                Mail::to('surajkumar00244vk@gmail.com')->send(new TextMessageMail([
                    'phone' => $request->phone
                ]));
            } else if($request->lead_area==1){
                Mail::to('surajkumar00244vk@gmail.com')->send(new ContactMail([
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'phone' => $request->phone
                ]));
            } else if($request->lead_area==2){
                Mail::to('surajkumar00244vk@gmail.com')->send(new CalculateMail([
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'courses' => $request->courses,
                    'amount' => $request->amount
                ]));
            }
            
            // Store data in Google Sheets
            $data=[
                [
                    $request->fullname ?? '',
                    $request->email ?? '',
                    $request->phone ?? '',
                    $request->courses ?? '',
                    $request->amount ?? '',
                    now()->toDateTimeString()
                    
                ]
            ];
            $range = 'apluspass!A2:D2';
            $this->googleSheetsService->appendDataToSheet($data,$range);

            return response()->json('success', 201);

        } catch (Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try{
            $lead = Lead::find($id);
            if (empty($lead)) throw new Exception('Lead not found', 200);

            return response()->json($lead);
        } catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try{
            $lead = Lead::find($id);
            if (empty($lead)) throw new Exception('Lead not found', 200);

            $lead->update($request->all());
            return response()->json($lead,200);
        } catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try{
            $lead = Lead::find($id);
            if (empty($lead)) throw new Exception('Lead not found', 200);

            $lead->delete();
            return response()->json(['message' => 'Lead deleted successfully'], 204);
        } catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    
}

