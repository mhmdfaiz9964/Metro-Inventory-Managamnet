<div class="p-3">
    <div class="mb-3">
        <label for="customerSearch" class="form-label fw-bold">Search / Select Customer</label>
        <input type="text" id="customerSearch" class="form-control" placeholder="Type to search customer...">
    </div>

    <div class="mb-3">
        <select id="customerSelect" class="form-select">
            <option value="">-- Select a customer --</option>
            @foreach($customers ?? [] as $customer)
                <option value="{{ $customer->id }}">
                    {{ $customer->name }} ({{ $customer->mobile_number ?? 'No mobile' }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="text-end">
        <button class="btn btn-primary" id="openLedgerBtn" disabled>
            <i data-feather="book-open"></i> Open Ledger
        </button>
    </div>
</div>

<script>
feather.replace();

// ✅ Initially enable dropdown if preloaded
$('#openLedgerBtn').prop('disabled', true);

// ✅ Typing triggers AJAX search
$('#customerSearch').on('input', function() {
    const query = $(this).val().trim();
    const dropdown = $('#customerSelect');

    if (query.length < 2) {
        // If less than 2 chars, restore default list
        dropdown.html(`
            <option value="">-- Select a customer --</option>
            @foreach($customers ?? [] as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->mobile_number ?? 'No mobile' }})</option>
            @endforeach
        `);
        $('#openLedgerBtn').prop('disabled', !dropdown.val());
        return;
    }

    $.get('{{ route('admin.customers.ledger.search') }}', { q: query }, function(data) {
        dropdown.empty().append('<option value="">-- Select a customer --</option>');

        if (data.length > 0) {
            data.forEach(c => {
                dropdown.append(`<option value="${c.id}">${c.name} (${c.mobile_number ?? 'No mobile'})</option>`);
            });
        } else {
            dropdown.append('<option value="">No customers found</option>');
        }
    });
});

// ✅ Enable button when selecting from dropdown
$('#customerSelect').on('change', function() {
    $('#openLedgerBtn').prop('disabled', !$(this).val());
});

// ✅ Redirect to ledger
$('#openLedgerBtn').on('click', function() {
    const id = $('#customerSelect').val();
    if (!id) return;
    window.location.href = `/admin/customers/${id}/history`;
});
</script>
