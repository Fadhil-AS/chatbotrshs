<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chatbot;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    // variable untuk menyimpan array faq
    // protected $faqs = [
    //     'jam operasional Rumah Sakit Hasan Sadikin' => [
    //         'category' => 'Jam operasional',
    //         'answer' => 'Jam operasional rumah sakit adalah Senin hingga Kamis: 07.00 – 15.30. dan Jumat: 07.00 – 16.00'
    //     ],
    //     'biaya konsultasi dokter' => [
    //         'category' => 'Biaya',
    //         'answer' => 'Biaya konsultasi dokter memiliki kategori yaitu Rawat Jalan Reguler dan Rawat Jalan Darurat dengan tarif Rp 175.000 hingga Rp 310.000'
    //     ],
    //     'cara mendaftar rawat inap' => [
    //         'category' => 'Pendaftaran',
    //         'answer' => 'Untuk mendaftar rawat inap, silahkan datang ke loket pendaftaran atau hubungi call center kami di 123-4567, untuk lebih lanjutnya dapat mengakses halaman https://reservasi.rshs.or.id/'
    //     ],
    // ];

    public function index()
    {
        return view('chatbot1');
    }

    private function jawabAcak()
    {
        $random = Chatbot::inRandomOrder()->limit(3)->get();

        if ($random->isEmpty()) {
            return response()->json(['answer' => 'Data jadwal tidak tersedia.']);
        }

        return response()->json(['answer' => $this->formatJawaban($random)]);
    }

    private function formatJawaban($data)
    {
        $response = '';
        foreach ($data as $item) {
            $response .= "<strong>{$item->hari_cluster}, {$item->tgl_cluster}</strong><br>";
            $response .= "Kelompok Staf Medis: {$item->ksm}<br>";
            $response .= "Poli: {$item->poli}<br>";
            $response .= "Dokter: {$item->dokter}<br>";
            $response .= "Jam: {$item->cluster}<br>";
            $response .= "Kuota: {$item->quota}<br>";
            $response .= "Informasi: {$item->informasi}<br><br>";
        }
        return $response;
    }


    public function ask(Request $request)
    {
        $pertanyaan = strtolower($request->input('question'));

        $chars = str_split($pertanyaan);
        $charLog = [];
        foreach ($chars as $index => $char) {
            $charLog[] = "$index => " . ord($char) . " (" . $char . ")";
        }

        // Cek sapaan umum
        $salam = ['halo', 'hi', 'hai', 'assalamualaikum', 'selamat pagi', 'selamat siang', 'selamat sore'];
        Carbon::setLocale('id');
        $hariIni = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
        foreach ($salam as $s) {
            if (Str::contains($pertanyaan, $s)) {
                return response()->json([
                    'answer' => "Halo! Saya chatbot jadwal dokter. Anda bisa tanya seperti: \"dokter bedah hari $hariIni\"."
                ]);
            }
        }

        $ksm = null;
        $poli = null;
        $hari = null;
        $log = [];

        $ksmKeywords = [
            'anestesi' => 'anestesiologi',
        ];

        $poliKeywords = [
            'klinik' => 'klinik anestesi & terapi nyeri',
            'terapi' => 'klinik anestesi & terapi nyeri'
        ];

        // Deteksi ksm
        foreach ($ksmKeywords as $key => $val) {
            if (preg_match("/{$key}/i", $pertanyaan)) {
                $ksm = $val;
                $log[] = "KSM cocok dengan: $key → $val";
                break;
            }
        }

        // Deteksi poli
        foreach ($poliKeywords as $key => $val) {
            if (preg_match("/{$key}/i", $pertanyaan)) {
                $poli = $val;
                break;
            }
        }

        // Deteksi hari dari "hari ini" dan "besok"
        if (preg_match("/\bhari ini\b/i", $pertanyaan)) {
            $hari = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
        }

        if (preg_match("/\bbesok\b/i", $pertanyaan)) {
            $hari = strtolower(Carbon::now()->addDay()->locale('id')->isoFormat('dddd'));
        }

        if (preg_match("/\blusa\b/i", $pertanyaan)) {
            $hari = strtolower(Carbon::now()->addDays(2)->locale('id')->isoFormat('dddd'));
        }

        // Deteksi hari
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        foreach ($hariList as $h) {
            if (preg_match("/\b{$h}\b/i", $pertanyaan)) {
                $hari = $h;
                break;
            }
        }

        // Jika KSM dan Hari cocok
        if (($ksm || $poli) && $hari) {
            $query = Chatbot::whereRaw('LOWER(hari_cluster) = ?', [$hari]);

            if ($ksm) {
                $query->whereRaw('LOWER(ksm) = ?', [strtolower($ksm)]);
            }

            if ($poli) {
                $query->whereRaw('LOWER(poli) LIKE ?', ['%' . strtolower($poli) . '%']);
            }

            $results = $query->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'answer' => "Data tidak ditemukan untuk jadwal pada hari *$hari*. Silakan coba hari lain."
                ]);
            }

            return response()->json(['answer' => $this->formatJawaban($results)]);
        }

        // Jika hanya hari disebutkan
        if ($hari && !$ksm && !$poli) {
            $results = Chatbot::whereRaw('LOWER(hari_cluster) = ?', [$hari])->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'answer' => "Data tidak ditemukan pada hari *$hari*. Silakan coba hari lain."
                ]);
            }

            return response()->json(['answer' => $this->formatJawaban($results)]);
        }

        if ($poli && !$hari) {
            $results = Chatbot::whereRaw('LOWER(poli) LIKE ?', ['%' . strtolower($poli) . '%'])->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'answer' => "Data tidak ditemukan untuk poli *$poli*. Silakan coba dengan hari tertentu."
                ]);
            }

            return response()->json(['answer' => $this->formatJawaban($results)]);
        }

        // Tidak ada keyword valid → jangan tampilkan data

        return response()->json([
            'answer' => "Pertanyaan Anda tidak dapat ditemukan jadwalnya. Silakan gunakan kata kunci seperti \"dokter anestesi hari $hariIni\"."
        ]);
    }

}
