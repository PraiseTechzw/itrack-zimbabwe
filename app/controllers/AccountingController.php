<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Invoice.php';
require_once dirname(__DIR__) . '/models/Payment.php';
require_once dirname(__DIR__) . '/models/Expense.php';
require_once dirname(__DIR__) . '/models/PettyCash.php';
require_once dirname(__DIR__) . '/models/CashBook.php';

class AccountingController extends Controller
{
    private Invoice $invoiceModel;
    private Payment $paymentModel;
    private Expense $expenseModel;
    private PettyCash $pettyCashModel;
    private CashBook $cashBookModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
        $this->expenseModel = new Expense();
        $this->pettyCashModel = new PettyCash();
        $this->cashBookModel = new CashBook();
    }

    public function index(): void
    {
        $this->requireRole(['Administrator', 'Finance Officer']);

        $summaries = [
            'invoices' => $this->invoiceModel->countInvoices(),
            'payments' => $this->paymentModel->countPayments(),
            'expenses' => $this->expenseModel->countExpenses(),
            'petty_cash' => $this->pettyCashModel->countEntries(),
            'cash_balance' => $this->cashBookModel->lastBalance(),
        ];

        $this->view('accounting/index', [
            'title' => 'Accounting',
            'summaries' => $summaries,
            'recentInvoices' => $this->invoiceModel->recent(),
            'recentPayments' => $this->paymentModel->recent(),
        ]);
    }
}
