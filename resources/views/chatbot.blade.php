<!DOCTYPE html>
<html>

<head>
    <title>Chatbot FAQ Rumah Sakit</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #chatbox {
            border: 1px solid #ccc;
            padding: 10px;
            width: 300px;
            height: 400px;
            overflow-y: scroll;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message strong {
            display: block;
        }
    </style>
</head>

<body>
    <h1>Chatbot FAQ Rumah Sakit</h1>
    <div id="chatbox"></div>
    <br>
    <input type="text" id="question" placeholder="Tulis pertanyaan Anda" style="width:300px;">
    <button id="send">Kirim</button>

    <script>
        $(document).ready(function() {
            $('#send').click(function() {
                var question = $('#question').val();
                if (question.trim() === "") {
                    return;
                }
                // Tampilkan pertanyaan di chatbox
                $('#chatbox').append('<div class="chat-message"><strong>Anda:</strong> ' + question +
                    '</div>');

                $.ajax({
                    url: '/chatbot',
                    method: 'POST',
                    data: {
                        question: question,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Tampilkan jawaban chatbot di chatbox
                        $('#chatbox').append(
                            '<div class="chat-message"><strong>Chatbot:</strong> ' +
                            response.answer + '</div>');
                        $('#question').val('');
                        // Scroll ke bawah chatbox
                        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
                    }
                });
            });
        });
    </script>
</body>

</html>
