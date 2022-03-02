<?php

namespace App\Http\Livewire\Tables;

use App\Traits\DataTableTrait;
use Exception;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Illuminate\Support\Facades\App;

class BaseDataTableComponent extends DataTableComponent
{

    use DataTableTrait;
    public array $perPageAccepted = [5, 10, 15, 20, 50, 100];
    public $checkDemo = false;
    public string $defaultSortColumn = 'id';
    public string $defaultSortDirection = 'desc';
    public bool $singleColumnSorting = true;

    protected $listeners = [
        'activateModel',
        'deactivateModel',
        'deleteModel',
        'filterUsers',
        'refreshTable' => '$refresh',
    ];


    public function searchView(): ?string
    {
        return null;
    }

    public function query()
    {
        return;
    }

    public function columns(): array
    {
        return [];
    }

    //Alert
    public function showSuccessAlert($message = "")
    {
        $this->alert('success', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
        ]);
    }

    public function showWarningAlert($message = "")
    {
        $this->alert('warning', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
        ]);
    }

    public function showErrorAlert($message = "")
    {
        $this->alert('error', "", [
            'position'  =>  'center',
            'text' => $message,
            'toast'  =>  false,
        ]);
    }
    //End Alert



    public $selectedModel;
    public $model;

    public function initiateActivate($id)
    {
        $this->selectedModel = $this->model::find($id);

        $this->confirm(__('Activate'), [
            'toast' => false,
            'text' =>  __('Are you sure you want to activate the selected data?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'activateModel',
            'onCancelled' => 'cancelled'
        ]);
    }

    public function initiateDeactivate($id)
    {
        $this->selectedModel = $this->model::find($id);

        $this->confirm(__('Deactivate'), [
            'toast' => false,
            'text' =>  __('Are you sure you want to deactivate the selected data?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deactivateModel',
            'onCancelled' => 'cancelled'
        ]);
    }

    public function initiateDelete($id)
    {
        $this->selectedModel = $this->model::find($id);

        $this->confirm(__('Delete'), [
            'toast' => false,
            'text' =>  __('Are you sure you want to delete the selected data?'),
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => __("Cancel"),
            'confirmButtonText' => __("Yes"),
            'onConfirmed' => 'deleteModel',
            'onCancelled' => 'cancelled'
        ]);
    }


    public function activateModel()
    {

        try {
            if ($this->checkDemo) {
                $this->isDemo();
            }
            $this->selectedModel->is_active = true;
            $this->selectedModel->save();
            $this->showSuccessAlert(__("Activated"));
        } catch (Exception $error) {
            $this->showErrorAlert("Failed");
        }
    }


    public function deactivateModel()
    {

        try {
            if ($this->checkDemo) {
                $this->isDemo();
            }
            $this->selectedModel->is_active = false;
            $this->selectedModel->save();
            $this->showSuccessAlert(__("Deactivated"));
        } catch (Exception $error) {
            $this->showErrorAlert("Failed");
        }
    }

    public function deleteModel()
    {

        try {
            $this->isDemo();
            $this->selectedModel->delete();
            $this->showSuccessAlert(__("Deleted"));
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? "Failed");
        }
    }




    public function isDemo($catchError = false)
    {
        if (!App::environment('production')) {
            $errorMessage = __("App is in demo version. Some changes can't be made");

            //
            if ($catchError) {
                $this->showErrorAlert($errorMessage);
            } else {
                throw new Exception($errorMessage);
            }
        };
    }

    public function inDemo()
    {
       return !App::environment('production');
    }
}
