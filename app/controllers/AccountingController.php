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

    private function requireAccountingAccess(): void
    {
        $this->requireModuleAccess('accounting', ['Administrator', 'Finance Officer']);
    }

    private function getInvoiceFields(): array
    {
        return [
            ['name' => 'sale_id', 'label' => 'Sale ID', 'type' => 'number', 'required' => true],
            ['name' => 'invoice_number', 'label' => 'Invoice Number', 'type' => 'text', 'required' => true],
            ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'date', 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['unpaid', 'paid', 'partial', 'overdue'], 'required' => true],
            ['name' => 'total_amount', 'label' => 'Total Amount', 'type' => 'number', 'step' => '0.01', 'required' => true],
        ];
    }

    private function getPaymentFields(): array
    {
        return [
            ['name' => 'invoice_id', 'label' => 'Invoice ID', 'type' => 'number'],
            ['name' => 'sale_id', 'label' => 'Sale ID', 'type' => 'number'],
            ['name' => 'paid_by', 'label' => 'Paid By (User ID)', 'type' => 'number'],
            ['name' => 'payment_date', 'label' => 'Payment Date', 'type' => 'date', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ['name' => 'method', 'label' => 'Method', 'type' => 'select', 'options' => ['cash', 'bank', 'mobile_money', 'card'], 'required' => true],
            ['name' => 'reference', 'label' => 'Reference', 'type' => 'text'],
        ];
    }

    private function getExpenseFields(): array
    {
        return [
            ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ['name' => 'paid_to', 'label' => 'Paid To', 'type' => 'text', 'required' => true],
            ['name' => 'paid_by', 'label' => 'Paid By (User ID)', 'type' => 'number'],
            ['name' => 'payment_date', 'label' => 'Payment Date', 'type' => 'date', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
        ];
    }

    private function getPettyCashFields(): array
    {
        return [
            ['name' => 'user_id', 'label' => 'User ID', 'type' => 'number'],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ['name' => 'transaction_date', 'label' => 'Transaction Date', 'type' => 'date', 'required' => true],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['expense', 'reimbursement', 'advance'], 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['posted', 'pending', 'reversed'], 'required' => true],
        ];
    }

    private function getCashBookFields(): array
    {
        return [
            ['name' => 'entry_date', 'label' => 'Entry Date', 'type' => 'date', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true],
            ['name' => 'debit', 'label' => 'Debit', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'credit', 'label' => 'Credit', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'balance', 'label' => 'Balance', 'type' => 'number', 'step' => '0.01', 'required' => true],
            ['name' => 'created_by', 'label' => 'Created By (User ID)', 'type' => 'number'],
        ];
    }

    public function index(): void
    {
        $this->requireAccountingAccess();

        $summaries = [
            'invoices' => $this->invoiceModel->countInvoices(),
            'invoice_total' => $this->invoiceModel->totalAmount(),
            'payments' => $this->paymentModel->countPayments(),
            'payment_total' => $this->paymentModel->totalAmount(),
            'expenses' => $this->expenseModel->countExpenses(),
            'expense_total' => $this->expenseModel->totalAmount(),
            'petty_cash' => $this->pettyCashModel->countEntries(),
            'petty_cash_total' => $this->pettyCashModel->totalAmount(),
            'cash_balance' => $this->cashBookModel->lastBalance(),
        ];

        $this->view('accounting/index', [
            'title' => 'Accounting',
            'summaries' => $summaries,
            'recentInvoices' => $this->invoiceModel->recent(),
            'recentPayments' => $this->paymentModel->recent(),
            'recentExpenses' => $this->expenseModel->recent(),
            'recentPettyCash' => $this->pettyCashModel->recent(),
            'recentCashBook' => $this->cashBookModel->recent(),
        ]);
    }

    public function invoices(): void
    {
        $this->requireAccountingAccess();
        $this->view('accounting/list', [
            'title' => 'Invoices',
            'section' => 'invoices',
            'resourceName' => 'Invoice',
            'items' => $this->invoiceModel->all(),
            'columns' => [
                ['label' => 'Invoice #', 'key' => 'invoice_number'],
                ['label' => 'Sale ID', 'key' => 'sale_id'],
                ['label' => 'Due Date', 'key' => 'due_date'],
                ['label' => 'Status', 'key' => 'status'],
                ['label' => 'Total', 'key' => 'total_amount', 'type' => 'money'],
            ],
            'createAction' => 'invoiceCreate',
            'editAction' => 'invoiceEdit',
            'deleteAction' => 'invoiceDelete',
        ]);
    }

    public function invoiceCreate(): void
    {
        $this->requireAccountingAccess();
        $this->csrfToken();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Create Invoice', 'mode' => 'create', 'section' => 'invoices', 'resourceName' => 'Invoice', 'fields' => $this->getInvoiceFields(), 'record' => $_POST, 'error' => 'Invalid security token', 'submitAction' => 'invoiceCreate']);
                return;
            }
            $this->invoiceModel->create($_POST);
            $this->redirect('/index.php?controller=accounting&action=invoices');
        }
        $this->view('accounting/form', ['title' => 'Create Invoice', 'mode' => 'create', 'section' => 'invoices', 'resourceName' => 'Invoice', 'fields' => $this->getInvoiceFields(), 'record' => [], 'submitAction' => 'invoiceCreate']);
    }

    public function invoiceEdit(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $this->csrfToken();
        $record = $this->invoiceModel->find($id);
        if (!$record) {
            $this->redirect('/index.php?controller=accounting&action=invoices');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Edit Invoice', 'mode' => 'edit', 'section' => 'invoices', 'resourceName' => 'Invoice', 'fields' => $this->getInvoiceFields(), 'record' => $record, 'error' => 'Invalid security token', 'submitAction' => 'invoiceEdit', 'recordId' => $id]);
                return;
            }
            $this->invoiceModel->update($id, $_POST);
            $this->redirect('/index.php?controller=accounting&action=invoices');
        }
        $this->view('accounting/form', ['title' => 'Edit Invoice', 'mode' => 'edit', 'section' => 'invoices', 'resourceName' => 'Invoice', 'fields' => $this->getInvoiceFields(), 'record' => $record, 'submitAction' => 'invoiceEdit', 'recordId' => $id]);
    }

    public function invoiceDelete(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->invoiceModel->delete($id);
        }
        $this->redirect('/index.php?controller=accounting&action=invoices');
    }

    public function payments(): void
    {
        $this->requireAccountingAccess();
        $this->view('accounting/list', [
            'title' => 'Payments',
            'section' => 'payments',
            'resourceName' => 'Payment',
            'items' => $this->paymentModel->all(),
            'columns' => [
                ['label' => 'Reference', 'key' => 'reference'],
                ['label' => 'Invoice ID', 'key' => 'invoice_id'],
                ['label' => 'Amount', 'key' => 'amount', 'type' => 'money'],
                ['label' => 'Method', 'key' => 'method'],
                ['label' => 'Date', 'key' => 'payment_date'],
            ],
            'createAction' => 'paymentCreate',
            'editAction' => 'paymentEdit',
            'deleteAction' => 'paymentDelete',
        ]);
    }

    public function paymentCreate(): void
    {
        $this->requireAccountingAccess();
        $this->csrfToken();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Create Payment', 'mode' => 'create', 'section' => 'payments', 'resourceName' => 'Payment', 'fields' => $this->getPaymentFields(), 'record' => $_POST, 'error' => 'Invalid security token', 'submitAction' => 'paymentCreate']);
                return;
            }
            $this->paymentModel->create($_POST);
            $this->redirect('/index.php?controller=accounting&action=payments');
        }
        $this->view('accounting/form', ['title' => 'Create Payment', 'mode' => 'create', 'section' => 'payments', 'resourceName' => 'Payment', 'fields' => $this->getPaymentFields(), 'record' => [], 'submitAction' => 'paymentCreate']);
    }

    public function paymentEdit(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $this->csrfToken();
        $record = $this->paymentModel->find($id);
        if (!$record) {
            $this->redirect('/index.php?controller=accounting&action=payments');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Edit Payment', 'mode' => 'edit', 'section' => 'payments', 'resourceName' => 'Payment', 'fields' => $this->getPaymentFields(), 'record' => $record, 'error' => 'Invalid security token', 'submitAction' => 'paymentEdit', 'recordId' => $id]);
                return;
            }
            $this->paymentModel->update($id, $_POST);
            $this->redirect('/index.php?controller=accounting&action=payments');
        }
        $this->view('accounting/form', ['title' => 'Edit Payment', 'mode' => 'edit', 'section' => 'payments', 'resourceName' => 'Payment', 'fields' => $this->getPaymentFields(), 'record' => $record, 'submitAction' => 'paymentEdit', 'recordId' => $id]);
    }

    public function paymentDelete(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->paymentModel->delete($id);
        }
        $this->redirect('/index.php?controller=accounting&action=payments');
    }

    public function expenses(): void
    {
        $this->requireAccountingAccess();
        $this->view('accounting/list', [
            'title' => 'Expenses',
            'section' => 'expenses',
            'resourceName' => 'Expense',
            'items' => $this->expenseModel->all(),
            'columns' => [
                ['label' => 'Category', 'key' => 'category'],
                ['label' => 'Paid To', 'key' => 'paid_to'],
                ['label' => 'Amount', 'key' => 'amount', 'type' => 'money'],
                ['label' => 'Date', 'key' => 'payment_date'],
                ['label' => 'Status', 'key' => 'description'],
            ],
            'createAction' => 'expenseCreate',
            'editAction' => 'expenseEdit',
            'deleteAction' => 'expenseDelete',
        ]);
    }

    public function expenseCreate(): void
    {
        $this->requireAccountingAccess();
        $this->csrfToken();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Create Expense', 'mode' => 'create', 'section' => 'expenses', 'resourceName' => 'Expense', 'fields' => $this->getExpenseFields(), 'record' => $_POST, 'error' => 'Invalid security token', 'submitAction' => 'expenseCreate']);
                return;
            }
            $this->expenseModel->create($_POST);
            $this->redirect('/index.php?controller=accounting&action=expenses');
        }
        $this->view('accounting/form', ['title' => 'Create Expense', 'mode' => 'create', 'section' => 'expenses', 'resourceName' => 'Expense', 'fields' => $this->getExpenseFields(), 'record' => [], 'submitAction' => 'expenseCreate']);
    }

    public function expenseEdit(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $this->csrfToken();
        $record = $this->expenseModel->find($id);
        if (!$record) {
            $this->redirect('/index.php?controller=accounting&action=expenses');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Edit Expense', 'mode' => 'edit', 'section' => 'expenses', 'resourceName' => 'Expense', 'fields' => $this->getExpenseFields(), 'record' => $record, 'error' => 'Invalid security token', 'submitAction' => 'expenseEdit', 'recordId' => $id]);
                return;
            }
            $this->expenseModel->update($id, $_POST);
            $this->redirect('/index.php?controller=accounting&action=expenses');
        }
        $this->view('accounting/form', ['title' => 'Edit Expense', 'mode' => 'edit', 'section' => 'expenses', 'resourceName' => 'Expense', 'fields' => $this->getExpenseFields(), 'record' => $record, 'submitAction' => 'expenseEdit', 'recordId' => $id]);
    }

    public function expenseDelete(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->expenseModel->delete($id);
        }
        $this->redirect('/index.php?controller=accounting&action=expenses');
    }

    public function pettyCash(): void
    {
        $this->requireAccountingAccess();
        $this->view('accounting/list', [
            'title' => 'Petty Cash',
            'section' => 'pettyCash',
            'resourceName' => 'Petty Cash',
            'items' => $this->pettyCashModel->all(),
            'columns' => [
                ['label' => 'User ID', 'key' => 'user_id'],
                ['label' => 'Type', 'key' => 'type'],
                ['label' => 'Amount', 'key' => 'amount', 'type' => 'money'],
                ['label' => 'Date', 'key' => 'transaction_date'],
                ['label' => 'Status', 'key' => 'status'],
            ],
            'createAction' => 'pettyCashCreate',
            'editAction' => 'pettyCashEdit',
            'deleteAction' => 'pettyCashDelete',
        ]);
    }

    public function pettyCashCreate(): void
    {
        $this->requireAccountingAccess();
        $this->csrfToken();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Create Petty Cash', 'mode' => 'create', 'section' => 'pettyCash', 'resourceName' => 'Petty Cash', 'fields' => $this->getPettyCashFields(), 'record' => $_POST, 'error' => 'Invalid security token', 'submitAction' => 'pettyCashCreate']);
                return;
            }
            $this->pettyCashModel->create($_POST);
            $this->redirect('/index.php?controller=accounting&action=pettyCash');
        }
        $this->view('accounting/form', ['title' => 'Create Petty Cash', 'mode' => 'create', 'section' => 'pettyCash', 'resourceName' => 'Petty Cash', 'fields' => $this->getPettyCashFields(), 'record' => [], 'submitAction' => 'pettyCashCreate']);
    }

    public function pettyCashEdit(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $this->csrfToken();
        $record = $this->pettyCashModel->find($id);
        if (!$record) {
            $this->redirect('/index.php?controller=accounting&action=pettyCash');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Edit Petty Cash', 'mode' => 'edit', 'section' => 'pettyCash', 'resourceName' => 'Petty Cash', 'fields' => $this->getPettyCashFields(), 'record' => $record, 'error' => 'Invalid security token', 'submitAction' => 'pettyCashEdit', 'recordId' => $id]);
                return;
            }
            $this->pettyCashModel->update($id, $_POST);
            $this->redirect('/index.php?controller=accounting&action=pettyCash');
        }
        $this->view('accounting/form', ['title' => 'Edit Petty Cash', 'mode' => 'edit', 'section' => 'pettyCash', 'resourceName' => 'Petty Cash', 'fields' => $this->getPettyCashFields(), 'record' => $record, 'submitAction' => 'pettyCashEdit', 'recordId' => $id]);
    }

    public function pettyCashDelete(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->pettyCashModel->delete($id);
        }
        $this->redirect('/index.php?controller=accounting&action=pettyCash');
    }

    public function cashBook(): void
    {
        $this->requireAccountingAccess();
        $this->view('accounting/list', [
            'title' => 'Cash Book',
            'section' => 'cashBook',
            'resourceName' => 'Cash Book',
            'items' => $this->cashBookModel->all(),
            'columns' => [
                ['label' => 'Date', 'key' => 'entry_date'],
                ['label' => 'Description', 'key' => 'description'],
                ['label' => 'Debit', 'key' => 'debit', 'type' => 'money'],
                ['label' => 'Credit', 'key' => 'credit', 'type' => 'money'],
                ['label' => 'Balance', 'key' => 'balance', 'type' => 'money'],
            ],
            'createAction' => 'cashBookCreate',
            'editAction' => 'cashBookEdit',
            'deleteAction' => 'cashBookDelete',
        ]);
    }

    public function cashBookCreate(): void
    {
        $this->requireAccountingAccess();
        $this->csrfToken();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Create Cash Book Entry', 'mode' => 'create', 'section' => 'cashBook', 'resourceName' => 'Cash Book', 'fields' => $this->getCashBookFields(), 'record' => $_POST, 'error' => 'Invalid security token', 'submitAction' => 'cashBookCreate']);
                return;
            }
            $this->cashBookModel->create($_POST);
            $this->redirect('/index.php?controller=accounting&action=cashBook');
        }
        $this->view('accounting/form', ['title' => 'Create Cash Book Entry', 'mode' => 'create', 'section' => 'cashBook', 'resourceName' => 'Cash Book', 'fields' => $this->getCashBookFields(), 'record' => [], 'submitAction' => 'cashBookCreate']);
    }

    public function cashBookEdit(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $this->csrfToken();
        $record = $this->cashBookModel->find($id);
        if (!$record) {
            $this->redirect('/index.php?controller=accounting&action=cashBook');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('accounting/form', ['title' => 'Edit Cash Book Entry', 'mode' => 'edit', 'section' => 'cashBook', 'resourceName' => 'Cash Book', 'fields' => $this->getCashBookFields(), 'record' => $record, 'error' => 'Invalid security token', 'submitAction' => 'cashBookEdit', 'recordId' => $id]);
                return;
            }
            $this->cashBookModel->update($id, $_POST);
            $this->redirect('/index.php?controller=accounting&action=cashBook');
        }
        $this->view('accounting/form', ['title' => 'Edit Cash Book Entry', 'mode' => 'edit', 'section' => 'cashBook', 'resourceName' => 'Cash Book', 'fields' => $this->getCashBookFields(), 'record' => $record, 'submitAction' => 'cashBookEdit', 'recordId' => $id]);
    }

    public function cashBookDelete(): void
    {
        $this->requireAccountingAccess();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->cashBookModel->delete($id);
        }
        $this->redirect('/index.php?controller=accounting&action=cashBook');
    }
}
