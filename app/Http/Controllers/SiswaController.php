<?php

namespace App\Http\Controllers;

use App\Kelas;
use App\Jurusan;
use App\Siswa;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->ajax()){
            $data = Siswa::with('user')->get();
            return datatables()->of($data)
                    ->addIndexColumn()
                    ->editColumn('nip', function($data){
                        return empty($data->nis) ? "Belum Diatur" : $data->nis;
                    })
                    ->addColumn('email', function($data){
                        return empty($data->user->email) ? "Belum Diatur" : $data->user->email;
                    })
                    ->addColumn('action', function($data){
                        $button = '<div class="btn-group" role="group" aria-label="Basic example">
                        <a href="'.route("siswa.edit",$data->id).'"class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                        <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$data->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteProduct"><i
                        class="fa fa-trash"></i></a>
                        <a href="'.route("siswa.show",$data->id).'"class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                                    </div>';
                        return $button;
                    })
                    ->rawColumns(['action','email','nip'])
                    ->make(true);
        }
        
        return view('admin.siswa.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $gender = ['Laki-laki','Perempuan'];
        $kelas = Kelas::with('jurusan')->get();
        $jurusan = Jurusan::all();
        return view('admin.siswa.form',compact('gender','kelas','jurusan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $siswa = Siswa::create($request->all());
        $user = User::create([
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);
        $siswa->user_id = $user->id;
        $siswa->save();
        return redirect()->route('siswa.index')->with('success','Berhasil menambah data');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        return view('admin.siswa.show',compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $siswa = Siswa::with('user')->findOrFail($id);        
        $gender = ['Laki-laki','Perempuan'];
        $kelas = Kelas::with('jurusan')->get();
        $jurusan = Jurusan::all();
        return view('admin.siswa.form',compact('siswa','gender','jurusan','kelas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $user = User::findOrFail($siswa->user_id);
        if ($request->password) {
            $user->update([
                'email'=>$request->email,
                'password'=>Hash::make($request->password)]);
        }else{
            $user->update(['email'=>$request->email]);
        }
        $siswa->update($request->all());
        return redirect()->route('siswa.index')->with('success','Berhasil merubah data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            if (auth()->user()->id==$id) {
                # code...
                return back()->withErrors(['Tidak Bisa Menghapus Akun yang sedang digunakan']);
            }
            $siswa = Siswa::find($id);
            User::find($siswa->user_id)->delete();
            $siswa->delete();
            return response()->json(['success'=>'berhasil menghapus data']);
        }
        
    }

    // public function export(){
    //     return (new FastExcel(Siswa::with('classroom')->get()))->download('users.xlsx', function ($data) {
    //         return [
    //             'NIS' => ($data->nip? $data->nip : " "),
    //             'Kelas' => ($data->classroom? $data->classroom->name : " "),
    //             'Nama' => $data->name,
    //             'Tanggal Lahir' => $data->born_date,
    //             'Tempat Lahir' => $data->born_city,
    //             'Alamat' => $data->address,
    //             'Jenis Kelamin' => $data->gender,
    //             'Golongan Darah' => $data->blood_type,
    //             'Asal Sekolah' => $data->school_from,
    //             'Nama Ayah' => $data->father_name,
    //             'Nama Ibu' => $data->mother_name,
    //             'Wali' => $data->guardian,
    //             'No BPJS' => $data->no_bpjs,
    //             'FASKES BPJS' => $data->faskes_bpjs,
    //         ];
    //     });
    // }
}
