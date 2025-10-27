<?php
session_start();
include '../includes/db.php'; // Update path if needed

header('Content-Type: application/json');

// Check if student logged in
if (!isset($_SESSION['emel'])) {
    echo json_encode(['type' => 'text', 'message' => 'You must be logged in.']);
    exit;
}

$pelajar_emel = $_SESSION['emel'];

// -------------- [NEW PART] Handle clicked activity button --------------
if (isset($_POST['activity_id'])) {
    $id = intval($_POST['activity_id']);

    // UPDATED: JOIN Persatuan to get organizer name
    $stmt = $conn->prepare("
        SELECT a.aktiviti_nama, a.tarikh_mula, a.aktiviti_tempat, p.persatuan_nama 
        FROM Aktiviti a
        JOIN Persatuan p ON a.persatuan_id = p.persatuan_id
        WHERE a.aktiviti_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = $result->fetch_assoc();

    if ($activity) {
        $formattedDate = date('d-m-Y', strtotime($activity['tarikh_mula']));
        $details = "🏆 <b>Nama Aktiviti:</b> " . htmlspecialchars($activity['aktiviti_nama']) . "<br>" .
                   "📅 <b>Tarikh:</b> " . $formattedDate . "<br>" .
                   "📍 <b>Tempat:</b> " . htmlspecialchars($activity['aktiviti_tempat']) . "<br>" .
                   "👥 <b>Penganjur:</b> " . htmlspecialchars($activity['persatuan_nama']);

        echo json_encode(['type' => 'html', 'message' => $details]);
        exit;
    }
}
// -------------- [END NEW PART] --------------

$input = strtolower(trim($_POST['message']));

// --- Check polite phrases like "thank you", "terima kasih" ---
$polite_words = ['terima kasih', 'thank you', 'thanks', 'baik', 'okey', 'ok'];

foreach ($polite_words as $word) {
    if (strpos($input, $word) !== false) {
        $thank_you_message = "Sama-sama! 🤗 Terima kasih kerana menggunakan chatbot ini. Jika ada lagi soalan, sila tanya saja ya!";

        // Save bot reply
        $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
        $stmt->bind_param("ss", $pelajar_emel, $thank_you_message);
        $stmt->execute();

        echo json_encode(['type' => 'text', 'message' => $thank_you_message]);
        exit;
    }
}


// Save student message
$stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'student', ?)");
$stmt->bind_param("ss", $pelajar_emel, $input);
$stmt->execute();

// --- Step 1: Check if input matches persatuan name directly ---
$stmt = $conn->prepare("SELECT * FROM Persatuan WHERE LOWER(persatuan_nama) = ?");
$stmt->bind_param("s", $input);
$stmt->execute();
$persatuanResult = $stmt->get_result();

if ($club = $persatuanResult->fetch_assoc()) {
    $details = "🏛️ <b>Nama Persatuan:</b> " . htmlspecialchars($club['persatuan_nama']) . "<br>" .
               "👤 <b>Pengerusi:</b> " . htmlspecialchars($club['pengerusi_nama']) . "<br>" .
               "👥 <b>Jumlah Ahli:</b> " . htmlspecialchars($club['jumlah_ahli']);



    $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
    $stmt->bind_param("ss", $pelajar_emel, $details);
    $stmt->execute();

    echo json_encode(['type' => 'text', 'message' => $details]);
    exit;
}

// --- Step 2: Check chatbot predefined questions ---
$stmt = $conn->prepare("SELECT * FROM Chatbot WHERE LOWER(question) = ?");
$stmt->bind_param("s", $input);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    if (!empty($row['target_table'])) {

        // --- If asking about Activities (Aktiviti)
        if ($row['target_table'] == 'Aktiviti') {
            $query = "SELECT aktiviti_id, aktiviti_nama, tarikh_mula FROM Aktiviti WHERE tarikh_mula >= CURDATE() ORDER BY tarikh_mula ASC LIMIT 3";
            $res = $conn->query($query);

            $activities = [];
            while ($act = $res->fetch_assoc()) {
                $formattedDate = date('d-m-Y', strtotime($act['tarikh_mula']));
                $activities[] = [
                    'id' => $act['aktiviti_id'],
                    'name' => $act['aktiviti_nama'] . " pada " . $formattedDate
                ];
            }

            if (empty($activities)) {
                $botReply = ['Tiada aktiviti akan datang buat masa ini.'];
                $botSave = implode(", ", $botReply);
                $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
                $stmt->bind_param("ss", $pelajar_emel, $botSave);
                $stmt->execute();

                echo json_encode(['type' => 'list', 'message' => $botReply]);
                exit;
            }

            // Otherwise, send buttons
            $buttons = [];
            foreach ($activities as $act) {
                $buttons[] = [
                    'label' => $act['name'],
                    'id' => $act['id']
                ];
            }

            $saveMsg = "Senarai aktiviti dipaparkan.";
            $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
            $stmt->bind_param("ss", $pelajar_emel, $saveMsg);
            $stmt->execute();

            echo json_encode([
                'type' => 'activity_buttons',
                'buttons' => $buttons
            ]);
            exit;
        }

        // --- If asking about Persatuan (Club)
        elseif ($row['target_table'] == 'Persatuan') {
            $query = "SELECT persatuan_nama FROM Persatuan";
            $res = $conn->query($query);
            $clubs = [];
            while ($club = $res->fetch_assoc()) {
                $clubs[] = $club['persatuan_nama'];
            }

            $botReply = empty($clubs) ? ["Tiada senarai persatuan buat masa ini."] : $clubs;
            $botSave = is_array($botReply) ? implode(", ", $botReply) : $botReply;

            $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
            $stmt->bind_param("ss", $pelajar_emel, $botSave);
            $stmt->execute();

            echo json_encode(['type' => 'buttons', 'buttons' => $botReply]);
            exit;
        }
    }

    // If static question with sub-questions
    if ($row['type'] == 'question') {
        $childs = $conn->prepare("SELECT question FROM Chatbot WHERE parent_id = ?");
        $childs->bind_param("i", $row['id']);
        $childs->execute();
        $childRes = $childs->get_result();

        $buttons = [];
        while ($child = $childRes->fetch_assoc()) {
            $buttons[] = $child['question'];
        }

        $prompt = (strtolower($row['question']) == 'hi') ? "Hai, apa yang saya boleh bantu?" : "Sila pilih pilihan berikut:";

        $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
        $stmt->bind_param("ss", $pelajar_emel, $prompt);
        $stmt->execute();

        echo json_encode([
            'type' => 'buttons',
            'buttons' => $buttons
        ]);
        exit;
    }

    // Normal answer
    else {
        $botReply = $row['answer'];
        $stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
        $stmt->bind_param("ss", $pelajar_emel, $botReply);
        $stmt->execute();

        echo json_encode(['type' => 'text', 'message' => $botReply]);
        exit;
    }
}
// --- Smart search for partial keyword ---
$stmt = $conn->prepare("SELECT question FROM Chatbot WHERE question LIKE ?");
$searchKeyword = "%" . $input . "%";
$stmt->bind_param("s", $searchKeyword);
$stmt->execute();
$searchResult = $stmt->get_result();

$suggestions = [];
while ($suggest = $searchResult->fetch_assoc()) {
    $suggestions[] = $suggest['question'];
}

if (!empty($suggestions)) {
    echo json_encode([
        'type' => 'buttons',
        'buttons' => $suggestions
    ]);
    exit;
}

// --- Step 3: No match found (Fallback) ---
$fallback = "Maaf, saya tidak menemui maklumat tersebut. Sila hubungi pengurus persatuan anda.";
$stmt = $conn->prepare("INSERT INTO ChatHistory (pelajar_emel, sender, message) VALUES (?, 'bot', ?)");
$stmt->bind_param("ss", $pelajar_emel, $fallback);
$stmt->execute();

echo json_encode(['type' => 'text', 'message' => $fallback]);
exit;
?>
