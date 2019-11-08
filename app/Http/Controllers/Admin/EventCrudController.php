<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\EventRequest as StoreRequest;
use App\Http\Requests\EventRequest as UpdateRequest;

/**
 * Class EventCrudController
 * An event corresponds to a class. This controller is used to monitor events automatically created
 * but also to add or update events manually.
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class EventCrudController extends CrudController
{

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:courses.edit']);
    }

    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        CRUD::setModel('App\Models\Event');
        CRUD::setRoute(config('backpack.base.route_prefix') . '/event');
        CRUD::setEntityNameStrings('event', 'events');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        CRUD::setColumns([
            
            [
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text'
            ],

            [
                // ROOM
                'label' => __("Course"), // Table column heading
                'type' => "select",
                'name' => 'course_id', // the column that contains the ID of that connected entity;
                'entity' => 'course', // the method that defines the relationship in your Model
                'attribute' => "name", // foreign key attribute that is shown to user
                'model' => "App\Models\Course", // foreign key model
                ],

            [
            'name' => "volume",
            'label' => __("Volume"), // Table column heading
            'type' => "model_function",
            'function_name' => 'getVolumeAttribute', // the method in your Model
            'suffix' => "h",
            ],

            [
            // TEACHER
            'label' => __("Teacher"), // Table column heading
            'type' => "select",
            'name' => 'teacher_id', // the column that contains the ID of that connected entity;
            'entity' => 'teacher', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => "App\Models\Teacher", // foreign key model
            ],

            [
            // ROOM
            'label' => __("Room"), // Table column heading
            'type' => "select",
            'name' => 'room_id', // the column that contains the ID of that connected entity;
            'entity' => 'room', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => "App\Models\Room", // foreign key model
            ],


            [
            'name' => "start", // The db column name
            'label' => __("Start Date"), // Table column heading
            'type' => "datetime",
                // 'format' => 'l j F Y', // use something else than the base.defauormat config value
            ],

            [
            'name' => "end", // The db column name
            'label' => __("End Date"), // Table column heading
            'type' => "datetime",
                // 'format' => 'l j F Y', // use something else than the base.default_date_format config value
            ],
             
        ]);


        // BP4 check if this works. The field has changed, perhaps the filter has changed too?
        CRUD::addFilter([ // daterange filter
            'type' => 'date_range',
            'name' => 'from_to',
            'label'=> __('Date range')
          ],
          false,
          function($value) { // if the filter is active, apply these constraints
            $dates = json_decode($value);
            CRUD::addClause('where', 'start', '>=', $dates->from);
            CRUD::addClause('where', 'start', '<=', $dates->to . ' 23:59:59');
          });

          CRUD::addFilter([ // daterange filter
            'type' => 'simple',
            'name' => 'orphan',
            'label'=> __('Events with no course')
          ],
          false,
          function($value) { // if the filter is active, apply these constraints
            $this->crud->query->where('course_id', null);
          });

          CRUD::addFilter([ // select2 filter
            'name' => 'teacher_id',
            'type' => 'select2',
            'label'=> __('Teacher')
          ], function() {
              return \App\Models\Teacher::all()->pluck('name', 'id')->toArray();
          }, function($value) { // if the filter is active
                  CRUD::addClause('where', 'teacher_id', $value);
          },
          function () { // if the filter is NOT active (the GET parameter "checkbox" does not exit)
            
        });
          


        CRUD::addFields([
            
            [
            'name' => 'name', // The db column name
            'label' => "Name", // Table column heading
            'type' => 'text'
            ],

            [
            // TEACHER
            'label' => "Teacher", // Table column heading
            'type' => "select",
            'name' => 'teacher_id', // the column that contains the ID of that connected entity;
            'entity' => 'teacher', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => "App\Models\Teacher", // foreign key model

            ],

            [
            // ROOM
            'label' => "Room", // Table column heading
            'type' => "select",
            'name' => 'room_id', // the column that contains the ID of that connected entity;
            'entity' => 'room', // the method that defines the relationship in your Model
            'attribute' => "name", // foreign key attribute that is shown to user
            'model' => "App\Models\Room", // foreign key model
            ],


            [
            'name' => "start", // The db column name
            'label' => "Start Date", // Table column heading
            'type' => "datetime",
            ],

            [
            'name' => "end", // The db column name
            'label' => "End Date", // Table column heading
            'type' => "datetime",
            ],
             
        ]);

        // add asterisk for fields that are required in EventRequest
        CRUD::setRequiredFields(StoreRequest::class, 'create');
        CRUD::setRequiredFields(UpdateRequest::class, 'edit');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(StoreRequest::class);
    }

    protected function setupUpdateOperation()
    {
        CRUD::setValidation(UpdateRequest::class);
    }
}
