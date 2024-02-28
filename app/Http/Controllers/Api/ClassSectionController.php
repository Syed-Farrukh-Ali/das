<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClassSectionRequest;
use App\Models\ClassSection;
use App\Repository\ClassSectionRepository;
use Illuminate\Http\Request;

class ClassSectionController extends BaseController
{
    public function __construct(ClassSectionRepository $classSectionRepository)
    {
        $this->classSectionRepository = $classSectionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function campusClassSections($campus_id, $education_type, $student_class_id = null)
    {
        if ($education_type == 1 && $student_class_id == null){
            return $this->sendError('Class id is required');
        }
        return $this->sendResponse($this->classSectionRepository->campusClassSections($campus_id, $education_type, $student_class_id), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function globalSections()
    {
        return $this->sendResponse($this->classSectionRepository->globalSections(), []);
    }


    public function addSectionToClass(ClassSectionRequest $request)
    {
        return $this->sendResponse($this->classSectionRepository->addSectionToClass($request), []);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ClassSection  $classSection
     * @return \Illuminate\Http\Response
     */
    public function show(ClassSection $classSection)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ClassSection  $classSection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClassSection $classSection)
    {
        //
    }

    /**
     * NOTE: the get api's are giving only global_section_id that way we can not delete class_section with that single id , so we have to search the defind section with 3 parameters.
     *
     * @param  \App\Models\ClassSection  $classSection
     * @return \Illuminate\Http\Response
     */
    public function destroy($campus_id, $student_class_id, $global_section_id)
    {
        $classSection = ClassSection::where(['campus_id' => $campus_id, 'student_class_id' => $student_class_id, 'global_section_id' => $global_section_id])->first();
        if ($classSection) {
            $deleted = $classSection->delete();
            if ($deleted) {
                return $this->sendResponse([], 'section is removed successfully', 200);
            }

            return $this->sendError('sorry! internal server error', [], 404);
        }

        return $this->sendError('sorry! No such record found', [], 404);
    }
}
