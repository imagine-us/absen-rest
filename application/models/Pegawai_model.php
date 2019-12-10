<?php

class Pegawai_model extends CI_Model
{
    public function getPegawai($nip = null)
    {
        if ($nip === null){
            return $this->db->get('pns')->result_array();    
        } else {
            return $this->db->get_where('pns',['PNS_PNSNIP' => $nip])->result_array();
        }
    }
    
    public function editProfile($id,$data)
    {
        $this->db->update('pns',$data,['id' => $id]);
        
        return $this->db->affected_rows();
    }
    
    public function getStatusId($pns_id,$tgl,$status)
    {
        $this->db->select('*');
        $this->db->from('status_absen');
        $this->db->where('pns_id =',$pns_id);
        $this->db->where('st_tgl =',$tgl);
        $this->db->where('st_status =', 0);
        return $this->db->get()->result_array();
    }
    
    public function insertAbsensi($data)
    {
        $array = array(
            'st_id' => $data['st_id'],
            'pns_id' => $data['pns_id'],
            'al_lat' => $data['lat'],
            'al_long' => $data['long'],
            'time' => $data['time'],
            'tanggal' => $data['tanggal'],
            'waktu' => $data['waktu']
        );
        $this->db->insert('absen_luar',$array);
            
        return $this->db->affected_rows();
    }
    
    public function updateStatusAbsensi($st_id)
    {
        $data = array(
            'st_status' => 1
        );
        
        $this->db->where('st_id', $st_id);
        $this->db->update('status_absen', $data);
        
        return $this->db->affected_rows();
    }
    
    public function getAbsensiHistori($id)
    {
        return $this->db->get_where('absen_luar',['pns_id' => $id])->result_array();
    }
    
    public function insertAduan($data)
    {
        $array = array(
            'id_user' => $data['pns_id'],
            'pengaduan' => $data['pengaduan'],
            'tanggal_pengaduan' => $data['tanggal_pengaduan']
        );
        $this->db->insert('pengaduan',$array);
            
        return $this->db->affected_rows();
    }
    
    public function getAduan($id)
    {
        return $this->db->get_where('pengaduan',['id_user'=>$id])->result_array();
    }
    
    public function getDetailAduan($id)
    {
        $this->db->select('p.pengaduan,p.tanggal_pengaduan,t.tanggapan,t.tanggal_tanggapan');
        $this->db->from('pengaduan p');
        $this->db->join('pengaduan_tanggapan t','p.id=t.id_pengaduan');
        $this->db->where('p.id =',$id);
        return $this->db->get()->result_array();
    }
    
    public function getPekerjaanHistori($nip,$bulan)
    {
        $this->db->select('k.id,k.pekerjaan_id,k.rincian_pekerjaan_id,p.nama_pekerjaan,k.tanggal,k.status');
        $this->db->from('pekerjaan p');
        $this->db->join('kegiatan k','p.id=k.pekerjaan_id');
        $this->db->where('k.pns_pnsnip =',$nip);
        $this->db->where('MONTH(k.tanggal) =',$bulan);
        $this->db->order_by('k.tanggal','desc');
        return $this->db->get()->result_array();
    }
    
     public function getPekerjaanHistoriStatus($nip,$status, $bulan)
    {
        $this->db->select('k.id,k.pekerjaan_id,k.rincian_pekerjaan_id,p.nama_pekerjaan,k.tanggal,k.status');
        $this->db->from('pekerjaan p');
        $this->db->join('kegiatan k','p.id=k.pekerjaan_id');
        $this->db->where('k.pns_pnsnip =',$nip);
        $this->db->where('k.status =',$status);
        $this->db->where('MONTH(k.tanggal) =',$bulan);
        return $this->db->get()->result_array();
    }
    
    public function getDetilPekerjaan($pid)
    {
        $this->db->select('p.id pekerjaan_id, r.id rincian_id, k.id kegiatan_id,
                            p.nama_pekerjaan,r.nama_rincian,k.nama_kegiatan,k.output,
                            k.tanggal,k.waktu_mulai,k.waktu_akhir');
        $this->db->from('pekerjaan p');
        $this->db->join('kegiatan k','p.id=k.pekerjaan_id');
        $this->db->join('rincian_pekerjaan r','p.id=r.id_pekerjaan');
        $this->db->where('p.id =',$pid);
        return $this->db->get()->result_array();
    }
    
    public function insertPekerjaan($data)
    {
        $array = array(
            'nama_pekerjaan' => $data['nama_pekerjaan']
        );
        if($this->db->insert('pekerjaan',$array)){
            $this->db->select('id');
            $this->db->from('pekerjaan');
            $this->db->order_by('id','desc');
            $this->db->limit(1);
            return $this->db->get()->result_array();    
        }
    }
    
    public function updatePekerjaan($pid,$data)
    {
        $this->db->update('kegiatan',$data,['id' => $pid]);
        
        return $this->db->affected_rows();
    }
    
    public function updateNamaPNS($nip,$data)
    {
        $this->db->update('pns',$data,['PNS_PNSNIP' => $nip]);
        
        return $this->db->affected_rows();
    }
    
    public function insertRincian($data)
    {
        $array = array(
            'id_pekerjaan' => $data['id_pekerjaan'],
            'nama_rincian' => $data['nama_rincian']
        );
        if($this->db->insert('rincian_pekerjaan',$array)){
            $this->db->select('id');
            $this->db->from('rincian_pekerjaan');
            $this->db->order_by('id','desc');
            $this->db->limit(1);
            return $this->db->get()->result_array();    
        }
    }
    
    public function insertKegiatan($data)
    {
        $array = array(
            'pns_pnsnip' => $data['pns_nip'],
            'pekerjaan_id' => $data['pekerjaan_id'],
            'rincian_pekerjaan_id' => $data['rincian_pekerjaan_id'],
            'tanggal' => $data['tanggal'],
            'waktu_mulai' => $data['waktu_mulai'],
            'waktu_akhir' => $data['waktu_akhir'],
            'nama_kegiatan' => $data['nama_kegiatan'],
            'output' => $data['output']
        );
        $this->db->insert('kegiatan',$array);
            
        return $this->db->affected_rows();
    }
    
    // public function getBawahan($nip)
    // {
    //     return $this->db->get_where('pns_atasan',['pns_atasan' => $nip])->result_array();
    // }
    
    public function getBawahan($nip)
    {
        $this->db->select('pas.PNS_PNSNIP, pas.pns_atasan, p.PNS_PNSNAM');
        $this->db->from('pns_atasan pas');
        $this->db->join('pns p','p.PNS_PNSNIP=pas.PNS_PNSNIP');
        $this->db->where('pas.pns_atasan =',$nip);
        return $this->db->get()->result_array();
    }
    
    public function insertAgenda($data)
    {
        $array = array(
            'id_user' => $data['pns_id'],
            'agenda' => $data['agenda'],
            'tanggal' => $data['tanggal']
        );
        $this->db->insert('agenda',$array);
            
        return $this->db->affected_rows();
    }
    
    public function getAgenda($id)
    {
        $this->db->select('id,id_user,agenda,tanggal');
        $this->db->from('agenda');
        $this->db->where('id_user',$id);
        $this->db->order_by('tanggal','desc');
        return $this->db->get()->result_array();
    }
}
?>