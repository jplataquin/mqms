<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceived;
use App\Models\PurchaseOrderReceivedItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderReceivedController extends Controller
{
    public function _list(Request $request)
    {
        $purchase_order_id = $request->query('purchase_order_id');

        if (!$purchase_order_id) {
            return response()->json([
                'status'  => 0,
                'message' => 'Purchase Order ID is required.',
                'data'    => []
            ]);
        }

        $receivedList = PurchaseOrderReceived::with(['Items.PurchaseOrderItem.MaterialItem', 'CreatedByUser'])
            ->where('purchase_order_id', $purchase_order_id)
            ->orderBy('received_date', 'DESC')
            ->get();

        return response()->json([
            'status' => 1,
            'message' => 'Success',
            'data'   => $receivedList
        ]);
    }

    public function _create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required|integer|exists:purchase_orders,id',
            'receipt_no'        => 'nullable|string|max:255',
            'received_date'     => 'required|date',
            'remarks'           => 'nullable|string',
            'items'             => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first(),
                'data'    => []
            ]);
        }

        $rawItems = $request->input('items');
        if (is_array($rawItems)) {
            $items = $rawItems;
        } elseif (is_string($rawItems)) {
            $items = json_decode($rawItems, true);
        } else {
            $items = null;
        }

        if (empty($items) || !is_array($items)) {
            return response()->json([
                'status'  => 0,
                'message' => 'At least one item must be received.',
                'data'    => []
            ]);
        }

        $purchaseOrder = PurchaseOrder::find($request->input('purchase_order_id'));

        // Validate PO is approved
        if ($purchaseOrder->status !== 'APRV') {
            return response()->json([
                'status'  => 0,
                'message' => 'Can only receive items for approved Purchase Orders.',
                'data'    => []
            ]);
        }

        // Validate items and quantities
        $poItems = $purchaseOrder->Items->keyBy('id');
        $validItemsToReceive = [];

        foreach ($items as $item) {
            $itemId = $item['purchase_order_item_id'] ?? null;
            $qty = (float) ($item['quantity_received'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            if (!isset($poItems[$itemId])) {
                return response()->json([
                    'status'  => 0,
                    'message' => "Item ID {$itemId} does not belong to this Purchase Order.",
                    'data'    => []
                ]);
            }

            $poItem = $poItems[$itemId];
            $remaining = $poItem->remaining_quantity;

            if ($qty > $remaining) {
                return response()->json([
                    'status'  => 0,
                    'message' => "Received quantity ({$qty}) exceeds remaining balance ({$remaining}) for item ID {$itemId}.",
                    'data'    => []
                ]);
            }

            $validItemsToReceive[] = [
                'purchase_order_item_id' => $itemId,
                'quantity_received'      => $qty,
            ];
        }

        if (empty($validItemsToReceive)) {
            return response()->json([
                'status'  => 0,
                'message' => 'No valid items with positive quantity received.',
                'data'    => []
            ]);
        }

        DB::beginTransaction();
        try {
            $received = PurchaseOrderReceived::create([
                'purchase_order_id' => $purchaseOrder->id,
                'receipt_no'        => $request->input('receipt_no'),
                'received_date'     => $request->input('received_date'),
                'remarks'           => $request->input('remarks'),
                'created_by'        => Auth::id() ?? 1,
            ]);

            foreach ($validItemsToReceive as $itemData) {
                PurchaseOrderReceivedItem::create([
                    'purchase_order_received_id' => $received->id,
                    'purchase_order_item_id'     => $itemData['purchase_order_item_id'],
                    'quantity_received'          => $itemData['quantity_received'],
                ]);
            }

            $purchaseOrder->updateReceivedStatus();

            DB::commit();

            return response()->json([
                'status'  => 1,
                'message' => 'Received record saved successfully.',
                'data'    => [
                    'received_id'     => $received->id,
                    'received_status' => $purchaseOrder->received_status,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 0,
                'message' => 'Failed to save received record: ' . $e->getMessage(),
                'data'    => []
            ]);
        }
    }
}
