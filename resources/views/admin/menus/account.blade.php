{{-- resources/views/admin/account-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Account Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Account Management</h4>

    <div class="row g-4">
        <!-- Account Management -->
        <div class="col-md-4">
            <a href="{{ route('admin.accounts.table') }}" class="text-decoration-none open-modal" data-title="Account Management">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="briefcase" class="text-primary" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Account Management</h6>
                    <p class="text-muted small mb-0">Manage all business accounts</p>
                </div>
            </a>
        </div>

        <!-- Receiving Payments -->
        <div class="col-md-4">
            <a href="{{ route('admin.receiving-payments.table') }}" class="text-decoration-none open-modal" data-title="Receiving Payments">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="download" class="text-success" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Receiving Payments</h6>
                    <p class="text-muted small mb-0">Record incoming payments</p>
                </div>
            </a>
        </div>

        <!-- Cheque in Hand -->
        <div class="col-md-4">
            <a href="{{ route('admin.receiving-cheques.index') }}" class="text-decoration-none" data-title="Cheque in Hand">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="file-text" class="text-info" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Cheque in Hand</h6>
                    <p class="text-muted small mb-0">Track cheques currently in hand</p>
                </div>
            </a>
        </div>

        <!-- Transactions -->
        <div class="col-md-4">
            <a href="{{ route('admin.transactions.table') }}" class="text-decoration-none open-modal" data-title="Transactions">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="shuffle" class="text-warning" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Transactions</h6>
                    <p class="text-muted small mb-0">View all account transactions</p>
                </div>
            </a>
        </div>

        <!-- Return Cheques -->
        <div class="col-md-4">
            <a href="{{ route('admin.return-cheques.table') }}" class="text-decoration-none open-modal" data-title="Return Cheques">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="rotate-ccw" class="text-secondary" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Return Cheques</h6>
                    <p class="text-muted small mb-0">Track returned cheques</p>
                </div>
            </a>
        </div>

        <!-- Cheque Management -->
        <div class="col-md-4">
            <a href="{{ route('admin.cheques.index') }}" class="text-decoration-none" data-title="Cheque Management">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="clipboard" class="text-dark" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Cheque It Out</h6>
                    <p class="text-muted small mb-0">Manage cheque records</p>
                </div>
            </a>
        </div>

        <!-- Fund Transfers -->
        <div class="col-md-4">
            <a href="{{ route('admin.fund-transfers.table') }}" class="text-decoration-none open-modal" data-title="Fund Transfers">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="repeat" class="text-primary" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Fund Transfers</h6>
                    <p class="text-muted small mb-0">Transfer funds between accounts</p>
                </div>
            </a>
        </div>

        <!-- Bank Accounts -->
        <div class="col-md-4">
            <a href="{{ route('admin.bank-accounts.table') }}" class="text-decoration-none open-modal" data-title="Bank Accounts">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="credit-card" class="text-success" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Bank Accounts</h6>
                    <p class="text-muted small mb-0">Manage all linked bank accounts</p>
                </div>
            </a>
        </div>

        <!-- Bank Crack -->
        <div class="col-md-4">
            <a href="#" class="text-decoration-none open-modal" data-title="Bank Crack">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="alert-triangle" class="text-danger" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Bank REC</h6>
                    <p class="text-muted small mb-0">Monitor bank security incidents / breaches</p>
                </div>
            </a>
        </div>

        <!-- Loan -->
        <div class="col-md-4">
            <a href="{{ route('admin.loans.index') }}" class="text-decoration-none" data-title="Loan">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="dollar-sign" class="text-primary" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Credit History</h6>
                    <p class="text-muted small mb-0">Manage Credit and repayment schedules</p>
                </div>
            </a>
        </div>
        <!-- Loan -->
        <div class="col-md-4">
            <a href="{{ route('admin.customer-loans.index') }}" class="text-decoration-none" data-title="Loan">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="credit-card" class="text-primary" width="40" height="40"></i>
                    </div>
                    <h6 class="fw-bold">Loans & Investment </h6>
                    <p class="text-muted small mb-0">Manage Loans and repayment schedules</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
/* Custom modal border and shadow */
#markPaidModal .modal-content {
    border: 2px solid #340965; /* Bootstrap primary color */
    border-radius: 12px;        /* Rounded corners */
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3); /* Soft shadow */
    padding: 0;
    overflow: hidden;
}

/* Optional: Header styling */
#markPaidModal .modal-header {
    background-color: #340965 ;
    color: #fff;
    border-bottom: 2px solid #340965 ;
}

/* Optional: Footer styling */
#markPaidModal .modal-footer {
    border-top: 2px solid #340965;
}

/* Optional: Buttons with subtle hover effect */
#markPaidModal .btn-success {
    transition: all 0.3s ease;
}

#markPaidModal .btn-success:hover {
    background-color: #198754;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

</style>

<!-- Universal Modal -->
@include('layouts.model')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
feather.replace();
$(document).on('click', '#transactions-table .pagination a', function(e) {
    e.preventDefault();
    let url = $(this).attr('href');

    $.ajax({
        url: url,
        type: 'GET',
        success: function(res) {
            // Replace table content in the modal
            $('#transactions-table').html(res);
        },
        error: function(err) {
            console.error(err);
        }
    });
});

// Universal modal for all account tables
$('.open-modal').on('click', function(e) {
    e.preventDefault();

    const url = $(this).attr('href');
    const title = $(this).data('title');

    $('#universalModal .modal-title').text(title);

    if (title === 'Bank Crack') {
        $('#universalModalBody').html('<div class="text-center py-5"><h5 class="text-muted">Coming Soon!</h5><p class="text-muted">This feature is under development.</p></div>');
        $('#universalModal').modal('show');
    } else {
        $('#universalModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

        // Show modal first to initialize the instance
        $('#universalModal').modal('show');

        // Now safely get and modify the modal instance to allow nesting
        const modalElement = document.getElementById('universalModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance._enforceFocus = function() {};
        }

        $.get(url, function(data) {
            $('#universalModalBody').html(data);
            // Re-bind handlers after content loads (including cheque-specific ones)
            bindChequeHandlers();
        }).fail(function() {
            $('#universalModalBody').html('<div class="alert alert-danger">Error loading content.</div>');
        });
    }
});

// Bind handlers for cheque-specific actions (e.g., Mark as Paid) - call after AJAX
function bindChequeHandlers() {
    // Mark as Paid button handler (event delegation within loaded content)
    $('#universalModal').off('click.markPaid').on('click.markPaid', '.btn-mark-paid', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = $(this);
        const chequeId = btn.data('id');

        // Set values for Mark Paid Modal
        $('#modal_cheque_id').val(chequeId);
        $('#modal_paid_date').val(new Date().toISOString().split('T')[0]);  // Today's date

        // Show the nested modal without hiding outer
        const markPaidModalEl = document.getElementById('markPaidModal');
        if (markPaidModalEl) {
            const markPaidModal = new bootstrap.Modal(markPaidModalEl, {
                backdrop: true,
                keyboard: true
            });
            markPaidModal.show();
        }
    });

    // Handle form submission via AJAX
    $('#markPaidForm').off('submit.markPaid').on('submit.markPaid', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const chequeId = $('#modal_cheque_id').val();

        fetch(`/admin/receiving-cheques/mark-received/${chequeId}`, {
            method: 'POST',
            body: formData,  // Use FormData for easier handling (includes all fields/CSRF)
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // Better CSRF handling
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Update row dynamically
                const row = document.getElementById(`cheque-row-${chequeId}`);
                if (row) {
                    row.querySelector('td:nth-child(6)').innerHTML = '<span class="badge bg-success">Paid</span>';
                    row.querySelector('td:nth-child(7)').textContent = $('#modal_paid_date').val();
                    const markPaidBtn = row.querySelector('.btn-mark-paid');
                    if (markPaidBtn) markPaidBtn.remove();
                }
                // Close nested modal
                $('#markPaidModal').modal('hide');
                // Success SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Cheque marked as paid successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                // Error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: res.message || 'Something went wrong!'
                });
            }
        })
        .catch(err => {
            console.error(err);
            // Catch Error SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Request Failed!',
                text: 'An unexpected error occurred. Please try again.'
            });
        });
    });

    // Delete handler (example) - Upgraded to SweetAlert confirm
    $('#universalModal').off('click.delete').on('click.delete', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This cheque will be permanently deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#delete-form-${id}`).submit();
            }
        });
    });
}

// Initial bind (for non-AJAX loads)
$(document).ready(function() {
    bindChequeHandlers();
});
</script>
@endsection