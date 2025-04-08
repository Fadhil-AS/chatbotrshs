<!DOCTYPE html>
<html>

<head>
    <title>Chatbot FAQ Rumah Sakit</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/chatbot.css') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <!-- Chatbot -->
    <div id="chatbot-container" class="chatbot-container">
        <div class="chatbot-header">
            <h5>Chatbot</h5>
            <button id="close-chatbot" class="close-chatbot">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
        <div class="chatbot-body">
            <div id="chatbot-messages" class="chatbot-messages"></div>
            <div class="chatbot-input-container">
                <input type="text" id="chatbot-input" class="chatbot-input" placeholder="Mulai bertanya.." />
                <button id="send-chatbot" class="send-btn">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </div>
    </div>
    <button id="open-chatbot" class="open-chatbot-btn">
        <i class="bi bi-chat-dots"></i>
    </button>

    <!-- Script Chatbot -->
    <script>
        $(document).ready(function() {
            // Tampilkan chatbot dengan animasi
            $('#open-chatbot').click(function() {
                const chatbot = $('#chatbot-container');
                const icon = $(this).find('i');

                if (chatbot.hasClass('show')) {
                    chatbot.slideUp(() => {
                        chatbot.removeClass('show');
                        icon.removeClass('bi-x').addClass('bi-chat-dots');
                    });
                } else {
                    chatbot.addClass('show').hide().slideDown(() => {
                        icon.removeClass('bi-chat-dots').addClass('bi-x');
                    });
                }
            });

            // Tutup chatbot dengan animasi
            $('#close-chatbot').click(function() {
                $('#chatbot-container').slideUp(() => {
                    $('#chatbot-container').removeClass('show');
                });
            });

            // Fokus input - styling ditangani oleh CSS
            $('#chatbot-input').on('focus', function() {
                $(this).addClass('focus');
            }).on('blur', function() {
                $(this).removeClass('focus');
            });

            // Klik tombol kirim
            $('#send-chatbot').click(function() {
                sendMessage();
            });

            // Fungsi kirim pertanyaan
            $('#chatbot-input').keypress(function(e) {
                if (e.which === 13) {
                    sendMessage();
                }
            });

            // Fungsi kirim chat
            function sendMessage() {
                const question = $('#chatbot-input').val().trim();
                if (question === "") return;

                const now = new Date();
                const time = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                $('#chatbot-messages').append(`
                    <div class="chat-message user">
                        <div class="chat-message-content">${question}</div>
                        <div class="chat-timestamp">${time}</div>
                        <i class="bi bi-person-circle chat-message-icon"></i>
                    </div>
                `);

                $('#chatbot-input').val('');
                $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);



                // setTimeout(() => {
                //     $('#chatbot-messages').append(`
            //     <div class="chat-message bot">
            //         <i class="bi bi-robot chat-message-icon"></i>
            //         <div>
            //             <div class="chat-message-content">Ini adalah respon dari chatbot untuk pengecekan tampilan.</div>
            //             <div class="chat-timestamp left">${time}</div>
            //         </div>
            //     </div>
            // `);
                //     $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
                // }, 500);

                $.ajax({
                    url: '/chatbot',
                    method: 'POST',
                    data: {
                        question: question,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $('#chatbot-messages').append(`
                            <div class="chat-message bot">
                                <div class="chat-message-content">${res.answer}</div>
                                <div class="chat-timestamp">${time}</div>
                            </div>
                        `);
                        $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
    </script>
</body>

</html>
