<?php

namespace Cyaxaress\Payment\Repositories;

use Carbon\Carbon;
use Cyaxaress\Payment\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Integer;

class PaymentRepo
{
    private $query;
    public function __construct()
    {
        $this->query = Payment::query();
    }

    public function store($data)
    {
        return Payment::create([
            "buyer_id" => $data['buyer_id'],
            "paymentable_id" => $data['paymentable_id'],
            "paymentable_type" => $data['paymentable_type'],
            "amount" => $data['amount'],
            "invoice_id" => $data['invoice_id'],
            "gateway" => $data['gateway'],
            "status" => $data['status'],
            "seller_p" => $data['seller_p'],
            "seller_share" => $data['seller_share'],
            "site_share" => $data['site_share'],
        ]);
    }

    public function findByInvoiceId($invoiceId)
    {
        return Payment::where('invoice_id', $invoiceId)->first();
    }

    public function changeStatus($id, string $status)
    {
        return Payment::where("id", $id)->update([
            "status" => $status
        ]);
    }

    public function searchEmail($email)
    {
        if (!is_null($email)) {
            $this->query->join("users", "payments.buyer_id", 'users.id')->select("payments.*", "users.email")->where("email", "like", "%" . $email . "%");
        }

        return $this;
    }

    public function searchAmount($amount)
    {
        if (!is_null($amount)) {
            $this->query->where("amount",  $amount);
        }

        return $this;
    }


    public function searchInvoiceId($invoiceId)
    {
        if (!is_null($invoiceId)) {
            $this->query->where("invoice_id", "like", "%" .  $invoiceId . "%");
        }

        return $this;
    }
    public function searchAfterDate($date)
    {
        if (!is_null($date)) {
            $this->query->whereDate("created_at", ">=", $date);
        }

        return $this;
    }

    public function searchBeforeDate($date)
    {
        if (!is_null($date)) {
            $this->query->whereDate("created_at", "<=", $date);
        }

        return $this;
    }



    public function paginate()
    {
        return $this->query->latest()->paginate();
    }

    public function getLastNDaysPayments($status, $days = null)
    {
        $query = Payment::query();

        if (!is_null($days)) $query = $query->where("created_at", ">=", now()->addDays($days));

        return $query->where("status", $status)->latest();
    }

    public function getLastNDaysSuccessPayments($days = null)
    {
        return $this->getLastNDaysPayments(Payment::STATUS_SUCCESS, $days);
    }

    public function getLastNDaysTotal($days = null)
    {
        return $this->getLastNDaysSuccessPayments($days)->sum("amount");
    }

    public function getLastNDaysSiteBenefit($days = null)
    {
        return $this->getLastNDaysSuccessPayments($days)->sum("site_share");
    }

    public function getLastNDaysSellerShare($days = null)
    {
        return $this->getLastNDaysSuccessPayments($days)->sum("seller_share");
    }

    public function getDayPayments($day, $status)
    {
        return $query = Payment::query()->whereDate("created_at", $day)->where("status", $status)->latest();
    }

    public function getDaySuccessPayments($day)
    {
        return $this->getDayPayments($day, Payment::STATUS_SUCCESS);
    }

    public function getDayFailedPayments($day)
    {
        return $this->getDayPayments($day, Payment::STATUS_FAIL);
    }

    public function getDaySuccessPaymentsTotal($day)
    {
        return $this->getDaySuccessPayments($day)->sum("amount");
    }

    public function getDayFailedPaymentsTotal($day)
    {
        return $this->getDayFailedPayments($day)->sum("amount");
    }

    public function getDaySiteShare($day)
    {
        return $this->getDaySuccessPayments($day)->sum("site_share");
    }

    public function getDaySellerShare($day)
    {
        return $this->getDaySuccessPayments($day)->sum("seller_share");
    }

    public function getDailySummery(Collection $dates)
    {
        return Payment::query()->where("created_at", ">=", $dates->keys()->first())
            ->groupBy("date")
            ->orderBy("date")
            ->get([
                DB::raw("DATE(created_at) as date"),
                DB::raw("SUM(amount) as totalAmount"),
                DB::raw("SUM(seller_share) as totalSellerShare"),
                DB::raw("SUM(site_share) as totalSiteShare"),
            ]);
    }
}