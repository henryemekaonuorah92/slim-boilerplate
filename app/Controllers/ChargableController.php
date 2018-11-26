<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\Chargables\ChargableRepositoryInterface;
use App\Responses\ApiResponse;
use App\Services\ChargableService;
use App\Transformers\Chargables\ChargableTransformer;
use App\Validators\Chargables\StoreChargableValidator;
use App\Validators\Chargables\UpdateChargableValidator;
use Slim\Http\Request;
use Slim\Http\Response;

class ChargableController
{
    /**
     * @var ApiResponse
     */
    private $apiResponse;
    /**
     * @var ChargableRepositoryInterface
     */
    private $chargableRepository;
    /**
     * @var ChargableTransformer
     */
    private $chargableTransformer;

    /**
     * ChargableController constructor.
     *
     * @param ApiResponse $apiResponse
     * @param ChargableRepositoryInterface $chargableRepository
     * @param ChargableTransformer $chargableTransformer
     */
    public function __construct(ApiResponse $apiResponse, ChargableRepositoryInterface $chargableRepository, ChargableTransformer $chargableTransformer)
    {
        $this->apiResponse = $apiResponse;
        $this->chargableRepository = $chargableRepository;
        $this->chargableTransformer = $chargableTransformer;
    }

    /**
     * Return list of chargables
     *
     * @return Response
     */
    public function index(): Response
    {
        if (!$chargables = $this->chargableRepository->index()) {
            return $this->apiResponse->error('Chargables not found', 'List of chargable is not available or not exists', 404, $chargables);
        }

        $data = $this->chargableTransformer->collection($chargables);

        return $this->apiResponse->success($data);
    }

    /**
     * Get a specific Chargable
     *
     * @param string $id
     *
     * @return Response
     */
    public function show(string $id): Response
    {
        if (!$chargable = $this->chargableRepository->show($id)) {
            return $this->apiResponse->error('Chargable not found', 'The chargable is not available or not exists', 404, $chargable);
        }

        $data = $this->chargableTransformer->item($chargable);

        return $this->apiResponse->success($data);
    }

    /**
     * Get Chargables by Branch
     *
     * @param int $branchId
     *
     * @return Response
     */
    public function getByBranch(string $branchId): Response
    {
        if (!$chargable = $this->chargableRepository->getByBranch($branchId)) {
            return $this->apiResponse->error('Chargable not found', 'The chargable is not available or not exists', 404, $chargable);
        }

        $data = $this->chargableTransformer->collection($chargable);

        return $this->apiResponse->success($data);
    }


    /**
     * Get Chargables by Account Officer
     *
     * @param int $accountOfficerId
     *
     * @return Response
     */
    public function getByAccountOfficer(string $accountOfficerId): Response
    {
        if (!$chargable = $this->chargableRepository->getByAccountOfficer($accountOfficerId)) {
            return $this->apiResponse->error('Chargable not found', 'The chargable is not available or not exists', 404, $chargable);
        }

        $data = $this->chargableTransformer->collection($chargable);

        return $this->apiResponse->success($data);
    }

     /**
     * Add a new Chargable
     *
     * @param Request $request
     * @param StoreChargableValidator $validator
     * @param ChargableService $chargableService
     *
     * @return Response
     */
    public function store(Request $request, StoreChargableValidator $validator, ChargableService $chargableService): Response
    {
        if (!$validator->validate()) {
            return $this->apiResponse->errorValidation($validator->errors());
        }

        if (!$chargable = $chargableService->store($request->getParams())) {
            return $this->apiResponse->error('Chargable not created', 'The chargable has not been created', 500, $chargable);
        }

        $data = $this->chargableTransformer->item($chargable);

        return $this->apiResponse->success($data, 201);
    }

     /**
     * Sync online db in batch
     *
     * @param Request $request
     * @param ChargableService $chargableService
     *
     * @return Response
     */
    public function batchSync(Request $request, ChargableService $chargableService): Response
    {
        $entries = $request->getParsedBody();
        // $entries = $entries['data'];
        $returnValue = [];
        $num = 0;
        
        foreach ($entries as $entry) {

            // $entry = json_decode($entry);
            // $entry = json_decode(json_encode($entry), true);
            $validator = new \Valitron\Validator($entry, [], 'en');
            
            if ($entry['sync_flag'] == 3) {
                $returnValue = $this->deleteForBatch($returnValue, $entry['tran_id']);
            }          
            elseif ($entry['sync_flag'] == 1) {

                $storeChargableValidator = new \App\Validators\Chargables\StoreChargableValidator($validator);

                $returnValue = $this->storeForBatch($returnValue, $entry['tran_id'], $entry, $storeChargableValidator, $chargableService);
               
            }         
            elseif ($entry['sync_flag'] == 2) {
                $updateChargableValidator = new \App\Validators\Chargables\UpdateChargableValidator($validator);

                $returnValue = $this->updateForBatch($returnValue, $entry['tran_id'], $entry, $updateChargableValidator, $chargableService);
            }
            $num ++;
        }

        return $this->apiResponse->success($returnValue);

    }

    /**
     * Update chargable data
     *
     * @param int $id
     * @param Request $request
     * @param UpdateChargableValidator $validator
     * @param ChargableService $chargableService
     *
     * @return Response
     */
    public function update(string $id, Request $request, UpdateChargableValidator $validator, ChargableService $chargableService): Response
    {
        if (!$validator->validate()) {
            return $this->apiResponse->errorValidation($validator->errors());
        }

        if (!$chargable = $chargableService->update($id, $request->getParams())) {
            return $this->apiResponse->error('Chargable not updated', 'The chargable not exists or has not been updated', 500, $chargable);
        }

        return $this->apiResponse->success('Chargable updated');
    }

    /**
     * Delete Chargable
     *
     * @param int $id
     *
     * @return Response
     */
    public function delete(string $id): Response
    {
        if (!$chargable = $this->chargableRepository->delete($id)) {
            return $this->apiResponse->error('Chargable not deleted', 'The chargable not exists or has not been deleted', 500, $chargable);
        }

        return $this->apiResponse->success('Chargable deleted');
    }

    /**
     * Prepare batch return message
     *
     * @param array $returnable
     * @param int $id
     * @param string $operation
     * @param int $responceType
     *
     * @return Response Array
     */
    private function parseBatchReturnMessage(array $returnable, string $id, string $operation, int $responceType)
    {
        $returnInstance = array("tran_id"=>$id, "responce"=>$responceType, "action"=>$operation);
        $returnable[] = $returnInstance;

        return $returnable;
    }

    /**
     * Delete Chargable for Batch operation
     *
     * @param array $returnValue
     * @param int $id
     *
     * @return Response Array
     */
    private function deleteForBatch(array $returnValue, string $id)
    {
        if (!$chargable = $this->chargableRepository->delete($id)) {
            $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "delete", 500);
        }
        else {
            $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "delete", 200);
        }

        return $returnValue;
    }

    /**
     * Store Chargable for Batch operation
     *
     * @param array $returnValue
     * @param int $id
     * @param array $entry
     * @param StoreChargableValidator $storeChargableValidator
     * @param ChargableService $chargableService
     * 
     * @return Response Array
     */
    private function storeForBatch(array $returnValue, string $id, array $entry, StoreChargableValidator $storeChargableValidator, ChargableService $chargableService)
    {                
        if (!$storeChargableValidator->validate()) {
            $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "insert", 400);
        }
        else {
            if (!$chargable = $chargableService->store($entry)) {
                $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "insert", 500);                        
            }
            else {
                $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "insert", 200); 
            }
        }

        return $returnValue;
    }

    /**
     * Update Chargable for Batch operation
     *
     * @param array $returnValue
     * @param int $id
     * @param array $entry
     * @param UpdateChargableValidator $updateChargableValidator
     * @param ChargableService $chargableService
     * 
     * @return Response Array
     */
    private function updateForBatch(array $returnValue, string $id, array $entry, UpdateChargableValidator $updateChargableValidator, ChargableService $chargableService)
    {                
        if (!$updateChargableValidator->validate()) {
            $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "validate", 400);                    
        }
        else {
            if (!$chargable = $chargableService->update($id, $entry)) {
    
                $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "update", 500);
            }
            else {
                $returnValue = $this->parseBatchReturnMessage($returnValue, $id, "update", 200);
            }
        }

        return $returnValue;
    }

}
