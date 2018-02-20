<?php namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\EloquentUserRepository;
use App\Http\Transformers\UserTransformer;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use App\Http\Utilities\PushNotification;

class AuthController extends BaseApiController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->userRepository   = new EloquentUserRepository();
        $this->transformer      = new UserTransformer();
    }

    /**
     * Register User
     *
     * @param Request $request
     * @return json|string
     */
    public function register(Request $request)
    {
        //Validate Request
        $validator = Validator::make($request->all(), [
            'phone'             => 'required',
            'password'          => 'required',
            'name'              => 'required',
            'home_latitude'     => 'required',
            'home_longitude'    => 'required',
            'home_address_name' => 'required',
            'home_address'      => 'required'
        ]);

        if ($validator->fails())
        {
            return $this->failureResponse([], 'Invalid Input Parameters.');
        }

        if(!($request->header('devicetype') && $request->header('devicetoken')))
        {
            return $this->failureResponse([], 'Invalid Header Parameters.');
        }

        //Define data and check for Duplicate
        $data                   = $request->all();
        $data['device_type']    = $request->header('devicetype');
        $data['device_token']   = $request->header('devicetoken');
        $guardians              = isset($data['guardians']) ? json_decode($data['guardians'], true) : [];
        $children               = isset($data['children']) ? json_decode($data['children'], true) : [];
        $phoneNumberList        = [$data['phone']];

        // Check for at least one child
        if(empty($children))
        {
            return $this->failureResponse([], 'Please provide at least one child detail.');
        }

        $checkForDuplicatePhone = $this->userRepository->checkForDuplicatePhone($data['phone']);

        if($checkForDuplicatePhone)
        {
            return $this->failureResponse([], 'User Already Exist');
        }

        if(!empty($guardians))
        {
            foreach($guardians as $singeGuardian)
            {
                $checkForDuplicateGuardian = $this->userRepository->checkForDuplicatePhone($singeGuardian['phone']);

                if($checkForDuplicateGuardian)
                {
                    return $this->failureResponse([], 'Guardian with same Mobile Number Already Exist');
                }

                if(in_array($singeGuardian['phone'], $phoneNumberList))
                {
                    return $this->failureResponse([], 'Request contains repeating phone number');
                }
                else
                {
                    $phoneNumberList[] = $singeGuardian['phone'];
                }
            }
        }

        foreach($children as $singeChild)
        {
            $checkForDuplicateChild = $this->userRepository->checkForDuplicatePhone($singeChild['phone']);

            if($checkForDuplicateChild)
            {
                return $this->failureResponse([], 'Child with same Mobile Number Already Exist');
            }

            if(in_array($singeChild['phone'], $phoneNumberList))
            {
                return $this->failureResponse([], 'Request contains repeating phone number');
            }
            else
            {
                $phoneNumberList[] = $singeChild['phone'];
            }
        }

        $admin = $this->userRepository->createParent($data);

        return $this->login($admin->code, 'Registration Successful.', $request);
    }

    /**
     * Login
     *
     * @param $code
     * @param null $message
     * @param Request $request
     * @return json|string
     */
    public function login($code, $message = null, Request $request)
    {
        if (!$code)
        {
            return $this->failureResponse([], 'Invalid Input Parameters.');
        }

        if(!($request->header('devicetype') && $request->header('devicetoken')))
        {
            return $this->failureResponse([], 'Invalid Header Parameters.');
        }


        $user = $this->userRepository->getByCode($code);

        if(!$user)
        {
            return $this->failureResponse([], 'No Such user exist.');
        }

        try
        {
            $token                          = JWTAuth::fromUser($user);
            $familyData                     = $this->userRepository->fetchFamilyDataByFamilyCode($user->family_code)->toArray();
            $transformedUserData            = $this->transformer->transformUserWithToken($user->toArray(), $token);
            $transformedUserData['family']  = $this->transformer->transformCollection($familyData);

            $this->userRepository->updateDeviceToke($user->id, $request->header('devicetype'), $request->header('devicetoken'));
        }
        catch (JWTException $e)
        {
            return $this->failureResponse([], 'Error in creating token.');
        }

        return $this->successResponse($transformedUserData, $message ? $message : 'Login Successful.');
    }

    /**
     * Admin Login
     *
     * @param Request $request
     * @return json|string
     */
    public function AdminLogin(Request $request)
    {
        //Validate Request
        $validator = Validator::make($request->all(), [
            'phone'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails())
        {
            return $this->failureResponse([], 'Invalid Input Parameters.');
        }

        $data       = $request->all();
        $loginFlag  = Auth::attempt(['phone' => $data['phone'], 'password' => $data['password']]);

        if(!$loginFlag)
        {
            return $this->failureResponse([], 'Invalid Credentials provided.');
        }

        return $this->login(Auth::user()->code, 'Login Successful', $request);
    }

    /**
     * Fetch Family
     *
     * @param Request $request
     * @param $code
     * @return json|string
     */
    public function fetchFamily(Request $request, $code)
    {
        $familyData = $this->userRepository->fetchFamilyDataByCode($code)->toArray();
        return $this->successResponse($this->transformer->transformCollection($familyData));
    }

    /**
     * Notification Test
     *
     * @param Request $request
     */
    public function notificationTest(Request $request)
    {
        $notificationClass = new PushNotification();
        $data = $request->all();
        $notificationClass->_pushNotification($data['message'], 'android', $data['devicetoken']);

        /*$registrationIds = $data['devicetoken'];
        #prep the bundle
        $msg = array
        (
            'body' 	=> $data['message'],
            'title'	=> $data['message'],
            'icon'	=> 'myicon',
            'sound' => 'mySound'
        );
        $fields = array
        (
            'to'		=> $registrationIds,
            'notification'	=> $msg
        );


        $headers = array
        (
            'Authorization: key=' . env('ANDROID_KEY'),
            'Content-Type: application/json'
        );
        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );*/

        dd('end');

    }
}
