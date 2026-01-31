@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h4 class="mb-4 fw-bold">Active Polls</h4>

    <div class="row">

        <!-- ================= LEFT : POLL LIST ================= -->
        <div class="col-md-4">
            @foreach(\App\Models\Poll::where('status','active')->get() as $poll)
                <button
                    class="btn btn-outline-primary w-100 text-start mb-2 poll-btn"
                    data-id="{{ $poll->id }}">
                    {{ $poll->question }}
                </button>
            @endforeach
        </div>

        <!-- ================= RIGHT : POLL DETAILS ================= -->
        <div class="col-md-8">
            <div id="pollView" class="card shadow-sm d-none">

                <div class="card-header bg-primary text-white fw-bold">
                    Poll Details
                </div>

                <div class="card-body">

                    <!-- QUESTION + OPTIONS -->
                    <div id="pollContent"></div>

                    <!-- SUBMIT BUTTON -->
                    <button id="voteBtn" class="btn btn-success mt-3">
                        Submit Vote
                    </button>

                    <!-- MESSAGE -->
                    <div id="voteMsg" class="mt-2 fw-bold"></div>

                    <!-- LIVE RESULTS -->
                    <hr>
                    <h6 class="fw-bold">Live Results</h6>
                    <div id="pollResults" class="mb-3 text-muted"></div>

                    <!-- ADMIN IP LIST -->
                    <hr>
                    <h6 class="fw-bold">Admin: IP List</h6>
                    <div id="ipList" class="text-muted"></div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection


@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let selectedPollId = null;
let resultInterval = null;

/* ======================
   LOAD POLL + OPTIONS
====================== */
$(document).on('click', '.poll-btn', function () {

    selectedPollId = $(this).data('id');

    $.get('/poll/' + selectedPollId, function (data) {

        let html = `<h5 class="mb-3 fw-bold">${data.question}</h5>`;

        data.options.forEach(opt => {
            html += `
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="option" value="${opt.id}">
                    <label class="form-check-label">
                        ${opt.option_text}
                    </label>
                </div>
            `;
        });

        $('#pollContent').html(html);
        $('#pollView').removeClass('d-none');
        $('#voteMsg').html('');

        if (resultInterval) clearInterval(resultInterval);

        loadResults();
        loadIpList();

        resultInterval = setInterval(() => {
            loadResults();
            loadIpList();
        }, 1000);
    });
});


/* ======================
   SUBMIT VOTE
====================== */
$(document).on('click', '#voteBtn', function () {

    let optionId = $('input[name="option"]:checked').val();

    if (!optionId) {
        alert('Please select an option');
        return;
    }

    $.ajax({
        url: '/vote',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            poll_id: selectedPollId,
            option_id: optionId
        },
        success: function (res) {
            $('#voteMsg')
                .html(res.message)
                .css('color', res.status ? 'green' : 'red');

            if (res.status) {
                loadResults();
                loadIpList();
            }
        },
        error: function (xhr) {
            alert('Vote failed : ' + xhr.status);
        }
    });
});


/* ======================
   LIVE RESULTS (MODULE 3)
====================== */
function loadResults() {

    $.get('/poll-results/' + selectedPollId, function (data) {

        let html = '';

        if (data.length === 0) {
            html = '<div>No votes yet</div>';
        }

        data.forEach(r => {
            html += `
                <div>
                    Option ID <b>${r.poll_option_id}</b> :
                    <b>${r.total}</b> votes
                </div>
            `;
        });

        $('#pollResults').html(html);
    });
}


/* ======================
   ADMIN IP LIST (MODULE 4)
====================== */
function loadIpList() {

    $.get('/admin/poll/' + selectedPollId + '/ips', function (data) {

        let html = '';

        if (data.length === 0) {
            html = '<div>No active IPs</div>';
        }

        data.forEach(ip => {
            html += `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span>${ip.ip_address}</span>
                    <button
                        class="btn btn-sm btn-danger releaseBtn"
                        data-ip="${ip.ip_address}">
                        Release
                    </button>
                </div>
            `;
        });

        $('#ipList').html(html);
    });
}


/* ======================
   RELEASE IP
====================== */
$(document).on('click', '.releaseBtn', function () {

    let ip = $(this).data('ip');

    $.ajax({
        url: '/admin/release-ip',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            poll_id: selectedPollId,
            ip: ip
        },
        success: function (res) {
            alert(res.message);
            loadResults();
            loadIpList();
        }
    });
});
</script>
@endpush
