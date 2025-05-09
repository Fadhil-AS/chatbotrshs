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

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <!-- Script Chatbot -->
    <script>
        const N8N_CHAT_URL = 'http://localhost:5678/webhook/eda3aa42-8079-4840-810a-f27dbd66b833/chat';
        const sessionId = Date.now().toString(); // unique per session

        $(function() {
            // Toggle chatbot open/close
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


            $('#close-chatbot').click(function() {
                $('#chatbot-container').slideUp().removeClass('show');
                $('#open-chatbot i').removeClass('bi-x').addClass('bi-chat-dots');
            });

            // Event listeners
            $('#send-chatbot').click(sendMessage);
            $('#chatbot-input').on('keypress', function(e) {
                if (e.which === 13) sendMessage();
            });

            async function sendMessage() {
                const input = $('#chatbot-input');
                const message = input.val().trim();
                if (!message) return;

                const time = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Tampilkan pesan user
                $('#chatbot-messages').append(`
                    <div class="chat-message user">
                        <div class="chat-message-content">${message}</div>
                        <div class="chat-timestamp">${time}</div>
                    </div>
                `);
                input.val('');
                $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);

                try {
                    const res = await fetch(N8N_CHAT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            sessionId,
                            action: 'sendMessage',
                            chatInput: message
                        })
                    });
                    let payload;
                    const text = await res.text();
                    try {
                        payload = JSON.parse(text);
                    } catch (err) {
                        console.error("Invalid JSON from server:", text);
                        throw new Error("Invalid JSON format received from server.");
                    }

                    // ambil jawaban
                    let reply = 'Maaf, tidak ada jawaban dari sistem.';
                    if (payload.answer) reply = payload.answer;
                    else if (payload.parameters?.answer) reply = payload.parameters.answer;
                    else if (payload[0]?.output) reply = payload[0].output;
                    else if (payload.output) reply = payload.output;


                    // tampilkan bot reply
                    const time = new Date().toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    $('#chatbot-messages').append(`
                      <div class="chat-message bot">
                        <div class="chat-message-content">${reply}</div>
                        <div class="chat-timestamp">${time}</div>
                      </div>
                    `);
                    $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);

                } catch (err) {
                    console.error('Chatbot Error:', err);
                    $('#chatbot-messages').append(`
                      <div class="chat-message bot">
                        <div class="chat-message-content">Maaf, terjadi kesalahan dalam sistem.</div>
                      </div>
                    `);
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
    </script>
</body>

</html>
