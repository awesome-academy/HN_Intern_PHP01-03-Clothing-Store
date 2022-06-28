import Pusher from "pusher-js";

$(document).ready(function () {
    function trans(key, replace = {}) {
        let translation = window.translationJsons[key] || key;

        for (var placeholder in replace) {
            translation = translation.replace(`:${placeholder}`, replace[placeholder]);
        }

        return translation;
    }

    var pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
        encrypted: true,
        cluster: "ap1",
    });

    console.log(window.user);
    var channel = pusher.subscribe("my-channel-" + window.user);
    channel.bind("my-event", async function (data) {
        let pending = parseInt($("#notification").find(".pending").html());

        if (Number.isNaN(pending)) {
            $(".notification-num").append(
                '<span class="pending badge btn-primary badge-number">1</span>'
            );
        } else {
            $(".pending")
                .html(pending + 1);
        }

        var status = '';

        switch (data.data.status) {
            case '1':
                status = 'noti_pending';
                break;
            case '2':
                status = 'noti_processing';
                break;
            case '3':
                status = 'noti_delivering';
                break;
            case '4':
                status = 'noti_complete';
                break;
            case '5':
                status = 'noti_cancel';
                break;
            case '6':
                status = 'noti_rejected';
                break;
        }

        let notificationBox = ` 
        <a href="/orderdetail/${data.id}">
        <li class="notification-box list" data-id="${data.data.notification_id}">
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-12 box-noti">
                    <div>
                        ${trans('your_order')} ${trans(status)}
                    </div>
                    <small class="box">${trans('recent')}</small>
                </div>
            </div>
        </li>
        </a>`;

        $(".show-notification").prepend(notificationBox);
    });

    $(document).on('click', '.notification-box', function (e) {
        let id = $(this).attr('data-id');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "GET",
            url: "/notification/read/" + id,
            success: function () {
                let pending = parseInt($("#notification").find(".pending").html());
                $("#notification")
                    .find(".pending")
                    .html(pending - 1);
            }
        });
    });

    $(document).on('click', '#readall', function (e) {
        e.preventDefault();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "GET",
            url: "/notification/readall",
            success: function () {
                window.location.reload();
            }
        });
    });
});
