<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

date_default_timezone_set('Asia/Jakarta');

class Pegawai extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pegawai_model','pegawai');
    }
    
    public function login_get(){
        $nip = $this->get('nip');
        $pswd = md5($this->get('password'));
        
        $pegawai = $this->pegawai->getPegawai($nip);
        
        if($pegawai)
        {
            foreach($pegawai as $row){
                if($row['PNS_PSWD'] == $pswd){
                    $this->response([
                            'status' => TRUE,
                            'id' => $row['id'],
                            'nip' => $row['PNS_PNSNIP'],
                            'nama' => $row['PNS_PNSNAM'],
                            'password' => $this->get('password'),
                            'foto' => $row['foto_url'],
                            'message' => 'Login berhasil'
                        ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Username/password salah'
                    ], REST_Controller::HTTP_BAD_REQUEST);        
                }
            }
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'User tidak ditemukan'
                ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    
    public function insertfoto_post(){
        $file_path = "http://tahutekno.com/ekinrest/foto/";
        // $var = $this->post('responsebody');
        $file_path = $file_path . basename( $_FILES['upload']['name']);
        if(move_uploaded_file($_FILES['upload']['name'], $file_path)) 
           {
                $this->response([
                        'status' => TRUE,
                        'message' => 'Upload foto berhasil',
                        'data' => $file_path
                    ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                        'status' => FALSE,
                        'message' => 'Upload foto gagal'
                    ], REST_Controller::HTTP_BAD_REQUEST);
            }
    }
    
    public function editprofile_put(){
        $id = $this->put('id');
        
        if($this->put('foto'))
        {
            $data = [
                'PNS_PNSNIP' => $this->put('nip'),
                'PNS_PSWD' => md5($this->put('password')),
                'foto_url' => $this->put('foto')
                ];
            
            if($this->pegawai->editProfile($id,$data)>0)
            {
                $this->response([
                        'status' => TRUE,
                        'message' => 'Update profil berhasil'
                    ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                        'status' => FALSE,
                        'message' => 'Update profil gagal'
                    ], REST_Controller::HTTP_BAD_REQUEST);
            }
        } else {
            $data = [
                'PNS_PNSNIP' => $this->put('nip'),
                'PNS_PSWD' => md5($this->put('password')),
                ];
            
            if($this->pegawai->editProfile($id,$data)>0)
            {
                $this->response([
                        'status' => TRUE,
                        'message' => 'Update profil berhasil'
                    ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                        'status' => FALSE,
                        'message' => 'Update profil gagal'
                    ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }
    
    public function cekabsen_get(){
        $pns_id = $this->get('id');
        $tgl = date('Y-m-d');
        $status = 0;
        
        $hasil_status = $this->pegawai->getStatusId($pns_id,$tgl,$status);
        
        if($hasil_status)
        {
            $this->response([
                    'status' => TRUE,
                    'data' => $hasil_status
                ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Tidak tersedia data absensi luar hari ini'
                ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    
    public function absensi_post(){
        $data = [
            'st_id' => $this->post('statusid'),
            'pns_id' => $this->post('pnsid'),
            'lat' => $this->post('latitude'),
            'long' => $this->post('longitude'),
            'time' => date('Y-m-d H:i:s'),
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s')
        ];
        
        if($this->pegawai->insertAbsensi($data)>0)
        {
            if($this->pegawai->updateStatusAbsensi($this->post('statusid'))>0)
            {
                $this->response([
                    'status' => TRUE,
                    'message' => 'Absensi berhasil'
                ], REST_Controller::HTTP_CREATED);    
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Absensi gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);    
            }
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Absensi gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function absensihist_get(){
        $id = $this->get('id');
        
        $data_absensi = $this->pegawai->getAbsensiHistori($id);
        
        if ($data_absensi)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $data_absensi
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data absensi tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function pekerjaanhist_get(){
        $nip = $this->get('nip');
        $bulan = $this->get('bulan');
        
        $data_pekerjaan = $this->pegawai->getPekerjaanHistori($nip,$bulan);
        
        if ($data_pekerjaan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $data_pekerjaan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data pekerjaan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function pekerjaanhiststatus_get(){
        $nip = $this->get('nip');
        $status = $this->get('status');
        $bulan = $this->get('bulan');
        
        $data_pekerjaan = $this->pegawai->getPekerjaanHistoriStatus($nip, $status, $bulan);
        
        if ($data_pekerjaan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $data_pekerjaan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data pekerjaan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function pekerjaandetail_get(){
        $pid = $this->get('pid');
        
        $detil_pekerjaan = $this->pegawai->getDetilPekerjaan($pid);
        
        if ($detil_pekerjaan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $detil_pekerjaan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data pekerjaan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function inputpekerjaan_post(){
        $pid = NULL; //id pekerjaan
        $rid = NULL; //id rincian pekerjaan
        
        $m = strtotime($this->post('waktumulai'));
        $waktu_mulai = date('Y-m-d H:i:s',$m);
        $waktu_selesai = new DateTime($waktu_mulai);
        $durasi = $this->post('durasi');
        $interval = $durasi." minutes";
        date_add($waktu_selesai,date_interval_create_from_date_string($interval));
        $t = strtotime($this->post('tgl'));
        $tanggal = date('Y-m-d H:i:s',$t);
        
        $data_pekerjaan = [
            'nama_pekerjaan' => $this->post('uraian'),
        ];
        
        $pekerjaan = $this->pegawai->insertPekerjaan($data_pekerjaan);
        if($pekerjaan)
        {
            foreach ($pekerjaan as $key => $value)
            {
                $pid = $value['id'];        
            }
            
            $data_rincian = [
                'id_pekerjaan' => $pid,
                'nama_rincian' => strtok($this->post('analisis'), '|')
            ];
            
            $rincian = $this->pegawai->insertRincian($data_rincian);
            if($rincian)
            {
                foreach ($rincian as $key => $value)
                {
                    $rid = $value['id'];        
                }
                
                $data_kegiatan = [
                    'pns_nip' => $this->post('nip'),
                    'pekerjaan_id' => $pid,
                    'rincian_pekerjaan_id' => $rid,
                    'tanggal' => $tanggal,
                    'waktu_mulai' => $this->post('waktumulai'),
                    'waktu_akhir' => date_format($waktu_selesai,'Y-m-d H:i:s'),
                    'durasi' => $this->post('durasi'),
                    'nama_kegiatan' => $this->post('pekerjaan'),
                    'output' => $this->post('hasil')
                ];
                
                if($this->pegawai->insertKegiatan($data_kegiatan)>0)
                {
                    $this->response([
                            'status' => TRUE,
                            'message' => 'Insert pekerjaan berhasil'
                        ], REST_Controller::HTTP_CREATED);
                } else {
                    $this->response([
                            'status' => FALSE,
                            'message' => 'Insert pekerjaan gagal'
                        ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Insert data pekerjaan gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function updatepekerjaan_put(){
        $pid = $this->put('id');
        
        $data = [
            'status' => $this->put('status')
            ];
        
        if($this->pegawai->updatePekerjaan($pid,$data)>0)
        {
            $this->response([
                    'status' => TRUE,
                    'message' => 'Update status pekerjaan berhasil'
                ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Update status pekerjaan gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function inputpengaduan_post(){
        $data = [
            'pns_id' => $this->post('pnsid'),
            'pengaduan' => $this->post('pengaduan'),
            'tanggal_pengaduan' => date('Y-m-d H:i:sa')
        ];
        
        if($this->pegawai->insertAduan($data)>0)
        {
            $this->response([
                    'status' => TRUE,
                    'message' => 'Pengaduan berhasil'
                ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Pengaduan gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function pengaduan_get(){
        $id = $this->get('pnsid');
        
        $data_aduan = $this->pegawai->getAduan($id);
        
        if ($data_aduan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $data_aduan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data aduan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function pengaduandetail_get(){
        $id = $this->get('aduanid');
        
        $detail_aduan = $this->pegawai->getDetailAduan($id);
        
        if ($detail_aduan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $detail_aduan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data aduan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function bawahan_get(){
        
        $nip = $this->get('nip');
        
        $bawahan = $this->pegawai->getBawahan($nip);
        
        if ($bawahan)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $bawahan
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data bawahan tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function pegawai_get(){
        
        $nip = $this->get('nip');
        
        if($nip===null){
            $pegawai = $this->pegawai->getPegawai();    
        } else {
            $pegawai = $this->pegawai->getPegawai($nip);
        }
        
        if ($pegawai)
            {
                // Set the response and exit
                $this->response([
                    'status' => TRUE,
                    'data' => $pegawai
                ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'User tidak ditemukan'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
    }
    
    public function inputagenda_post(){
        $t = strtotime($this->post('tanggal'));
        $tanggal = date('Y-m-d H:i:s',$t);
        
        $data = [
            'pns_id' => $this->post('pnsid'),
            'agenda' => $this->post('agenda'),
            'tanggal' => $tanggal
        ];
        
        if($this->pegawai->insertAgenda($data)>0)
        {
            $this->response([
                    'status' => TRUE,
                    'message' => 'Input agenda berhasil'
                ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Input agenda gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    public function agenda_get(){
        $id = $this->get('pnsid');
        
        $data_agenda = $this->pegawai->getAgenda($id);
        
        if ($data_agenda)
        {
            // Set the response and exit
            $this->response([
                'status' => TRUE,
                'data' => $data_agenda
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        } else
        {
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Data agenda tidak ditemukan'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
    
    public function updatenamapns_put(){
        $nip = $this->put('nip');
        
        $data = [
            'PNS_PNSNAM' => $this->put('nama')
            ];
        
        if($this->pegawai->updateNamaPNS($nip,$data)>0)
        {
            $this->response([
                    'status' => TRUE,
                    'message' => 'Update nama profil berhasil'
                ], REST_Controller::HTTP_CREATED);
        } else {
            $this->response([
                    'status' => FALSE,
                    'message' => 'Update nama profil gagal'
                ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
}
?>