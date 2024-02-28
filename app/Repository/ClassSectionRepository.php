<?php

namespace App\Repository;

use App\Http\Resources\GlobalSectionResource;
use App\Models\Campus;
use App\Models\ClassSection;
use App\Models\GlobalSection;
use App\Models\User;
use App\Repository\Interfaces\ClassSectionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClassSectionRepository extends BaseRepository implements ClassSectionRepositoryInterface
{
    /**
     * ProfileRepository constructor.
     *
     * @param  User  $model
     */
    public function __construct(Campus $model)
    {
        parent::__construct($model);
    }

    /**
     * classSections table store the information about how many section are added in a class
     * here we will search for a class of campus , get all global section ids and get the list
     * of section for that class from global_section table
     *
     * @param  int  $campus_id $student_id
     * @return \Illuminate\Http\Response list of section from global section
     */
    public function campusClassSections($campus_id, $education_type, $student_class_id = null)
    {
        $global_section_ids = ClassSection::where(function ($query) use ($student_class_id) {
            return  $student_class_id != null ? $query->where('student_class_id', $student_class_id) : '';
        })
            ->where('campus_id', $campus_id)
            ->where('education_type', $education_type)
            ->pluck('global_section_id')->toArray();

        return GlobalSectionResource::collection(GlobalSection::find($global_section_ids));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function globalSections()
    {
        return GlobalSectionResource::collection(GlobalSection::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addSectionToClass(Request $request)
    {
        DB::beginTransaction();
        try {
            ClassSection::firstOrCreate([
                'campus_id' => $request->campus_id,
                'student_class_id' => $request->student_class_id,
                'global_section_id' => $request->global_section_id,
                'education_type' => $request->education_type,
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return false;
        }
        DB::commit();

        return true;
    }

    // /**
    //  * @param  illuminate\Http\Request  $request
    //  *
    //  * @return bool
    //  * @throws \Throwable
    //  */
    // public function update(Request $request, Campus $campus)
    // {

    //     DB::beginTransaction();
    //     try {

    //         $campus->update([
    //             'name' => $request->name,
    //             'code' => $request->code,
    //             'area' => $request->area,
    //             'city' => $request->city,
    //             'province' => $request->province,
    //             'contact' => $request->contact,
    //         ]);
    //         $campus->user->update([
    //             'first_name' => $request->first_name,
    //             'last_name' => $request->last_name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //         ]);

    //     } catch (\Throwable $e) {
    //         dd($e);
    //         DB::rollBack();
    //         return false;
    //     }

    //     DB::commit();
    //     return new CampusResource($campus);

    // }

    // public function destroy(Campus $campus)
    // {
    //     $campus->user()->delete();
    //     $campus->delete();
    //     return response()->json('campus successfully deleted');
    // }
}
