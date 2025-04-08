<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    // variable untuk menyimpan array faq
    protected $faqs = [
        'jam operasional Rumah Sakit Hasan Sadikin' => [
            'category' => 'Jam operasional',
            'answer' => 'Jam operasional rumah sakit adalah Senin hingga Kamis: 07.00 – 15.30. dan Jumat: 07.00 – 16.00'
        ],
        'biaya konsultasi dokter' => [
            'category' => 'Biaya',
            'answer' => 'Biaya konsultasi dokter memiliki kategori yaitu Rawat Jalan Reguler dan Rawat Jalan Darurat dengan tarif Rp 175.000 hingga Rp 310.000'
        ],
        'cara mendaftar rawat inap' => [
            'category' => 'Pendaftaran',
            'answer' => 'Untuk mendaftar rawat inap, silahkan datang ke loket pendaftaran atau hubungi call center kami di 123-4567, untuk lebih lanjutnya dapat mengakses halaman https://reservasi.rshs.or.id/'
        ],
    ];

    public function index()
    {
        return view('chatbot1');
    }

    public function ask(Request $request)
    {
        $question = strtolower($request->input('question'));

        $categories = [];
        foreach ($this->faqs as $item) {
            $categories[] = $item['category'];
        }

        $categoriesString = implode(', ', $categories);
        $result = [
            'category' => 'Lainnya',
            'answer'   => "Maaf, saya tidak mengerti pertanyaan anda.\n Kategori pertanyaan yang tersedia: $categoriesString"
        ];

        // Balik logika pencocokan: cek apakah $question adalah substring dari $key
        foreach ($this->faqs as $key => $value) {
            if (strpos(strtolower($key), $question) !== false) {
                $result['category'] = $value['category'];
                $result['answer']   = $value['answer'];
                break;
            }
        }
        return response()->json($result);
    }
}
