<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Services\SetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SetupController extends Controller
{
    private SetupService $setup;

    public function __construct(SetupService $setup)
    {
        $this->setup = $setup;
    }

    // ── Step 1: Welcome + Requirements ────────────────────────────────────────

    public function welcome(Request $request)
    {
        $requirements = $this->setup->checkRequirements();
        $allPass      = $this->setup->allRequiredPass($requirements);

        // Mark that we've seen welcome so step 2 is unlocked
        $request->session()->put('setup_step', 2);

        return view('setup.welcome', compact('requirements', 'allPass'));
    }

    // ── Step 2: Database ──────────────────────────────────────────────────────

    public function database(Request $request)
    {
        if ($request->session()->get('setup_step', 0) < 2) {
            return redirect()->route('setup.welcome');
        }
        return view('setup.database');
    }

    public function testDatabase(Request $request)
    {
        // Form fields: db_host, db_port, db_name, db_user, db_pass
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
        ]);

        $result = $this->setup->testConnection([
            'host'     => $request->input('db_host'),
            'port'     => $request->input('db_port', '3306'),
            'database' => $request->input('db_name'),
            'username' => $request->input('db_user'),
            'password' => $request->input('db_pass', ''),
        ]);

        return response()->json($result);
    }

    public function saveDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
        ]);

        $host     = trim($request->input('db_host'));
        $port     = $request->input('db_port', '3306');
        $database = trim($request->input('db_name'));
        $username = trim($request->input('db_user'));
        $password = $request->input('db_pass', '');

        // Test connection
        $test = $this->setup->testConnection(compact('host', 'port', 'database', 'username', 'password'));
        if (!$test['ok']) {
            return back()->withErrors(['db' => 'Connection failed: ' . $test['message']])->withInput();
        }

        // Store in session for later steps
        $request->session()->put('setup_db', compact('host', 'port', 'database', 'username', 'password'));
        $request->session()->put('setup_step', 3);

        $envData = [
            'db_host'     => $host,
            'db_port'     => $port,
            'db_database' => $database,
            'db_username' => $username,
            'db_password' => $password,
            'app_url'     => $request->root(),
        ];

        $written = $this->setup->writeEnv($envData);

        if (!$written) {
            // .env not writable — show content for manual copy
            $envContent = $this->setup->generateEnvContent($envData);
            $request->session()->put('setup_env_content', $envContent);
            return redirect()->route('setup.account');
        }

        // Update DB config in-memory so migrations can run without restart
        Config::set('database.connections.mysql.host',     $host);
        Config::set('database.connections.mysql.port',     $port);
        Config::set('database.connections.mysql.database', $database);
        Config::set('database.connections.mysql.username', $username);
        Config::set('database.connections.mysql.password', $password);
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');

        // Run migrations
        if (!$this->setup->runMigrations()) {
            return back()->withErrors(['db' => 'Migrations failed. Please check your credentials and try again.'])->withInput();
        }

        // Generate app key if missing
        if (!config('app.key')) {
            $this->setup->generateAppKey();
        }

        return redirect()->route('setup.account');
    }

    // ── Step 3: Admin Account ─────────────────────────────────────────────────

    public function account(Request $request)
    {
        if ($request->session()->get('setup_step', 0) < 3) {
            return redirect()->route('setup.welcome');
        }
        $envContent = $request->session()->pull('setup_env_content');
        return view('setup.account', compact('envContent'));
    }

    public function saveAccount(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = $this->setup->createAdmin($data);
        $request->session()->put('setup_step', 4);
        $request->session()->put('setup_admin_id', $user->id);

        return redirect()->route('setup.site-settings');
    }

    // ── Step 4: Site Settings ─────────────────────────────────────────────────

    public function siteSettings(Request $request)
    {
        if ($request->session()->get('setup_step', 0) < 4) {
            return redirect()->route('setup.welcome');
        }
        $appUrl = $request->session()->get('setup_db.app_url', $request->root());
        return view('setup.site-settings', compact('appUrl'));
    }

    public function saveSiteSettings(Request $request)
    {
        $data = $request->validate([
            'site_name'    => 'required|string|max:255',
            'app_url'      => 'required|url',
            'timezone'     => 'required|string',
            'cors_origins' => 'nullable|string',
        ]);

        $this->setup->saveSettings($data);
        $request->session()->put('setup_step', 5);

        return redirect()->route('setup.complete');
    }

    // ── Step 5: Complete ──────────────────────────────────────────────────────

    public function complete(Request $request)
    {
        if ($request->session()->get('setup_step', 0) < 5) {
            return redirect()->route('setup.welcome');
        }

        $this->setup->markInstalled();

        // Clear setup session data
        $request->session()->forget(['setup_step', 'setup_db', 'setup_admin_id']);

        return view('setup.complete');
    }
}
