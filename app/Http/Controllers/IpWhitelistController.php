<?php

namespace App\Http\Controllers;

use App\Models\IpWhitelist;
use Illuminate\Http\Request;

class IpWhitelistController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth.basic'); // Password protection
    }

    public function index()
    {
        $ips = IpWhitelist::all();
        return view('admin.whitelist', compact('ips'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip|unique:ip_whitelists,ip_address'
        ]);

        IpWhitelist::create([
            'ip_address' => $request->ip_address,
        ]);

        return redirect()->route('admin.whitelist')->with('success', 'IP Address added to whitelist');
    }

    public function destroy($id)
    {
        $ip = IpWhitelist::findOrFail($id);
        $ip->delete();

        return redirect()->route('admin.whitelist')->with('success', 'IP Address removed from whitelist');
    }
}
