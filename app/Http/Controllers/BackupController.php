<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');

        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $files = collect(File::files($this->backupPath))
            ->filter(fn($file) => strtolower($file->getExtension()) === 'sql')
            ->sortByDesc(fn($file) => $file->getMTime())
            ->values();

        return view('backups.index', compact('files'));
    }

    public function create()
    {
        $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD', '');

        $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s %s > "%s"',
            $mysqldump,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            $dbPass !== '' ? '--password=' . escapeshellarg($dbPass) : '',
            escapeshellarg($dbName),
            $fullPath
        );

        @exec($command, $output, $resultCode);

        if (!File::exists($fullPath) || $resultCode !== 0) {
            return redirect()->route('backups.index')
                ->with('error', 'فشل إنشاء النسخة الاحتياطية. تأكد أن mysqldump متاح على الجهاز.');
        }

        return redirect()->route('backups.index')
            ->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح.');
    }

    public function download($filename)
    {
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($fullPath)) {
            return redirect()->route('backups.index')
                ->with('error', 'الملف غير موجود.');
        }

        return response()->download($fullPath);
    }

    public function restore($filename)
    {
        $fullPath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($fullPath)) {
            return redirect()->route('backups.index')
                ->with('error', 'ملف النسخة الاحتياطية غير موجود.');
        }

        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD', '');

        $mysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';

        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s %s < "%s"',
            $mysql,
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            $dbPass !== '' ? '--password=' . escapeshellarg($dbPass) : '',
            escapeshellarg($dbName),
            $fullPath
        );

        @exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            return redirect()->route('backups.index')
                ->with('error', 'فشل استرجاع النسخة الاحتياطية. تأكد أن mysql متاح على الجهاز.');
        }

        return redirect()->route('backups.index')
            ->with('success', 'تم استرجاع النسخة الاحتياطية بنجاح.');
    }
}