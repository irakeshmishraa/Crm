<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetConnection;
use App\Models\GoogleSheetSyncLog;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleSheetsController extends Controller
{
    public function index() { return view('google-sheets.index', ['connections' => GoogleSheetConnection::where('user_id', Auth::id())->with('syncLogs')->get()]); }
    public function connect(Request $request) { $request->validate(['spreadsheet_id' => 'required|string', 'spreadsheet_name' => 'required|string', 'sync_direction' => 'required|in:import,export,both', 'sync_type' => 'required|in:leads,followups,quotations,clients']); GoogleSheetConnection::create(['user_id' => Auth::id(), 'spreadsheet_id' => $request->spreadsheet_id, 'spreadsheet_name' => $request->spreadsheet_name, 'worksheet_name' => $request->worksheet_name, 'sync_direction' => $request->sync_direction, 'sync_type' => $request->sync_type, 'auto_sync' => $request->boolean('auto_sync')]); return back()->with('success', 'Connected.'); }
    public function spreadsheets() { return response()->json([]); }
    public function sync(Request $request) { $request->validate(['connection_id' => 'required|exists:google_sheet_connections,id']); $conn = GoogleSheetConnection::findOrFail($request->connection_id); try { $svc = new GoogleSheetsService(Auth::user()); $result = $svc->syncLeads($conn); GoogleSheetSyncLog::create(['connection_id' => $conn->id, 'direction' => $conn->sync_direction, 'records_processed' => $result['processed'] ?? 0, 'records_created' => $result['created'] ?? 0, 'status' => 'success']); $conn->update(['last_synced_at' => now()]); return back()->with('success', 'Sync completed.'); } catch (\Exception $e) { GoogleSheetSyncLog::create(['connection_id' => $conn->id, 'direction' => $conn->sync_direction, 'status' => 'failed', 'error_message' => $e->getMessage()]); return back()->withErrors(['sync' => 'Sync failed: ' . $e->getMessage()]); } }
    public function import(Request $request) { return $this->sync($request); }
    public function export(Request $request) { return $this->sync($request); }
    public function logs() { return view('google-sheets.logs', ['logs' => GoogleSheetSyncLog::whereHas('connection', fn($q) => $q->where('user_id', Auth::id()))->latest()->paginate(25)]); }
    public function saveMapping(Request $request) { $request->validate(['connection_id' => 'required|exists:google_sheet_connections,id', 'mapping' => 'required|array']); GoogleSheetConnection::findOrFail($request->connection_id)->update(['column_mapping' => $request->mapping]); return back()->with('success', 'Mapping saved.'); }
}
