<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('cari')){
		$data_siswa = \App\Siswa::where('nama_depan','LIKE','%' .$request->cari. '%')->get();
        }else{
            $data_siswa = \App\Siswa::all();
        }
        return view('siswa.index',['data_siswa'=>$data_siswa]);
    }

    public function create(Request $request)
    {
        $this->validate($request,[
            'nama_depan' => 'required|min:5',
            'nama_belakang' => 'required',
            'email' => 'required|email|unique:users',
            'jenis_kelamin' => 'required',
            'agama' => 'required',
            'avatar' => 'mimes:jpg,png',
        ]);
        //insert ke table users
        $user = new \App\User;
        $user->role = 'siswa';
        $user->name= $request->nama_depan;
        $user->email = $request->email;
        $user->password = bcrypt('qwerty');
        $user->remember_token = \Str::random(60);
        $user->save();

        //insert ke table siswa
        $request->request->add(['user_id' => $user->id ]);
        $siswa= \App\Siswa::create($request->all());
        if ($request->hasFile('avatar')) {
            $request->file('avatar')->move('images/',$request->file('avatar')->getClientOriginalName());
            $siswa->avatar = $request->file('avatar')->getClientOriginalName();
            $siswa->save();
        }
        return redirect('/siswa')->with('sukses','Data berhasil diinput');
    }

    public function edit($id)
    {
        $siswa = \App\Siswa::Find($id);
        return view('siswa/edit',['siswa' => $siswa]);
    }

    public function update(Request $request,$id)
    {
        //dd($request->all());
        $siswa = \App\Siswa::Find($id);
        $siswa->update($request->all());
        if ($request->hasFile('avatar')) {
            $request->file('avatar')->move('images/',$request->file('avatar')->getClientOriginalName());
            $siswa->avatar = $request->file('avatar')->getClientOriginalName();
            $siswa->save();
        }
        return redirect('/siswa')->with('sukses','Data berhasil diupdate');
    }

    public function delete(Request $request,$id)
    {
        $siswa = \App\Siswa::Find($id);
        $siswa->delete();
        return redirect('/siswa')->with('sukses','Data berhasil dihapus');
    }

     public function profile($id)
    {
        $siswa = \App\Siswa::Find($id);
        $matapelajaran = \App\Mapel::All();
        //dd($matapelajaran);
        return view('siswa.profile',['siswa' => $siswa,'matapelajaran' => $matapelajaran]);
    }

    public function addnilai(Request $request,$idsiswa)
    {
        $siswa = \App\Siswa::Find($idsiswa);
        if ($siswa->mapel()->where('mapel_id',$request->mapel)->exists()){
            return redirect('siswa/' .$idsiswa. '/profile')->with('error', 'Data mata pelajaran sudah ada.');
        }
        $siswa->mapel()->attach($request->mapel,['nilai' => $request->nilai]);

        return redirect('siswa/' .$idsiswa. '/profile')->with('sukses', 'Data nilai berhasil dimasukan');
    }

    public function deletenilai($idsiswa,$idmapel)
    {
        $siswa = \App\Siswa::Find($idsiswa);
        $siswa->mapel()->detach($idmapel);
        return redirect()->back()->with('sukses','Data nilai berhasil dihapus');
    }  
}

