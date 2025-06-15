<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use App\Models\ShippingTracking;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ShippingAddressController extends Controller
{
    protected $apiKey;
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_KEY');
        $this->baseUrl = env('RAJAONGKIR_URL');
        $this->client = new Client();
    }

    public function getProvinces()
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl . 'province', [
                'headers' => ['key' => $this->apiKey]
            ]);

            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCities(Request $request)
    {
        try {
            $provinceId = $request->query('province');
            $url = $this->baseUrl . 'city';
            if ($provinceId) {
                $url .= "?province={$provinceId}";
            }

            $response = $this->client->request('GET', $url, [
                'headers' => ['key' => $this->apiKey]
            ]);

            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function calculateShipping(Request $request)
    {
        try {
            $this->validate($request, [
                'origin' => 'required',
                'destination' => 'required',
                'weight' => 'required|integer|min:1',
                'courier' => 'required|in:jne,tiki,pos'
            ]);

            $response = $this->client->request('POST', $this->baseUrl . 'cost', [
                'headers' => ['key' => $this->apiKey],
                'form_params' => [
                    'origin' => $request->origin,
                    'destination' => $request->destination,
                    'weight' => $request->weight,
                    'courier' => $request->courier
                ]
            ]);

            return response()->json(json_decode($response->getBody()->getContents(), true));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'label' => 'required',
            'recipient_name' => 'required',
            'phone' => 'required',
            'province_id' => 'required',
            'province_name' => 'required',
            'city_id' => 'required',
            'city_name' => 'required',
            'full_address' => 'required',
            'postal_code' => 'required',
            'is_primary' => 'boolean'
        ]);

        if ($request->is_primary) {
            ShippingAddress::where('user_id', $request->user_id)
                ->update(['is_primary' => false]);
        }

        $address = ShippingAddress::create($request->all());

        return response()->json([
            'status' => 1,
            'message' => 'Shipping address created successfully',
            'address' => $address
        ], 201);
    }

    public function getUserAddresses($userId)
    {
        $addresses = ShippingAddress::where('user_id', $userId)->get();

        return response()->json([
            'status' => 1,
            'addresses' => $addresses
        ]);
    }

    public function getCouriers()
    {
        try {
            $couriers = [
                [
                    'code' => 'jne',
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                ],
                [
                    'code' => 'tiki',
                    'name' => 'Titipan Kilat (TIKI)',
                ],
                [
                    'code' => 'pos',
                    'name' => 'POS Indonesia',
                ]
            ];

            return response()->json([
                'status' => 1,
                'origin' => env('RAJAONGKIR_ORIGIN_ID'),
                'couriers' => $couriers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch couriers: ' . $e->getMessage()
            ], 500);
        }
    }
}
