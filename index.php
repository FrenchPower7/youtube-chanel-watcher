<?php
// Ta clé API YouTube (avec api_key pour la clé et channel_id pour ta chaine)
include 'key.php';

// URL pour obtenir les informations de la chaîne (nombre d'abonnés et image de profil)
$channel_url = "https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id=$channel_id&key=$api_key";
$videos_url = "https://www.googleapis.com/youtube/v3/search?channelId=$channel_id&part=id&type=video&order=date&maxResults=50&key=$api_key";

// Fonction pour obtenir les données de l'API
function get_data_from_api($url) {
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Récupération des infos de la chaîne
$channel_data = get_data_from_api($channel_url);
$nombre_abonnes = $channel_data['items'][0]['statistics']['subscriberCount'];
$profile_image_url = $channel_data['items'][0]['snippet']['thumbnails']['default']['url'];

// Récupération des IDs des vidéos
$videos_data = get_data_from_api($videos_url);
$video_ids = array_column($videos_data['items'], 'id');

// Récupérer toutes les vidéos et additionner les vues
$total_vues = 0;
foreach ($video_ids as $video_id) {
    $video_id = $video_id['videoId'];
    $video_stats_url = "https://www.googleapis.com/youtube/v3/videos?part=statistics&id=$video_id&key=$api_key";
    $video_data = get_data_from_api($video_stats_url);
    $total_vues += $video_data['items'][0]['statistics']['viewCount'];
}

// Objectif d'abonnés
$objectif_abonnes = 500;
$abonnes_restants = $objectif_abonnes - $nombre_abonnes;

// Calcul du pourcentage de progression
$pourcentage_progression = ($nombre_abonnes / $objectif_abonnes) * 100;

// Fonction pour enregistrer les données dans un fichier JSON
function log_data($nombre_abonnes, $total_vues) {
    $date = date('Y-m-d');

    // Définir le chemin du fichier JSON
    $file = 'data.json';

    // Lire le contenu du fichier JSON s'il existe
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    } else {
        $data = [];
    }

    // Trouver le dernier ID et incrémenter
    $last_id = count($data) > 0 ? end($data)['id'] : -1; // Commencer à 0
    $new_entry = [
        'id' => $last_id + 1,
        'date' => $date,
        'nombre_abonnes' => floatval($nombre_abonnes),
        'nombre_de_vues' => floatval($total_vues),
    ];

    // Ajouter la nouvelle entrée
    $data[] = $new_entry;

    // Sauvegarder les données dans le fichier JSON
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Vérifier si le bouton a été cliqué
if (isset($_POST['log_data'])) {
    log_data($nombre_abonnes, $total_vues);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma chaîne YouTube</title>
    <!-- Lien Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLmx1W9WIXtScuLAIgTJotazWfBAkM5EpLrkFbK0lSswKhHaOBb9o18I2cHIJz4+" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Ajout de Chart.js -->
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            background-color: #1e1e1e;
            border-radius: 10px;
            padding: 20px;
        }
        .progress-bar {
            background-color: #444;
        }
        .progress {
            background-color: #4caf50;
            height: 24px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        h2, h3 {
            color: #ffffff;
        }
        .profile-image {
            border-radius: 50%;
        }
        a {
            color: #007bff;
        }
        a:hover {
            color: #0056b3;
        }
        canvas {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Bienvenue sur ma chaîne YouTube !</h2>

        <!-- Première section : Informations sur la chaîne -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center">
                <img src="<?php echo $profile_image_url; ?>" alt="Photo de profil" class="profile-image mb-3">
                <h3>Infos de la chaîne</h3>
                <p>Nombre d'abonnés : <strong><?php echo $nombre_abonnes; ?></strong></p>
                <p>Nombre total de vues : <strong><?php echo $total_vues; ?></strong></p>
            </div>
        </div>

        <!-- Deuxième section : Progression vers les 500 abonnés -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6">
                <h3>Progression vers 500 abonnés</h3>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $pourcentage_progression; ?>%;"></div>
                </div>
                <p class="mt-2">Il vous manque <strong><?php echo $abonnes_restants; ?></strong> abonnés pour atteindre 500 abonnés.</p>
            </div>
        </div>

        <!-- Troisième section : Lien vers la chaîne -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center">
                <h3>Accéder à ma chaîne YouTube</h3>
                <a href="https://www.youtube.com/channel/<?php echo $channel_id; ?>" target="_blank" class="btn btn-primary">Cliquez ici pour visiter ma chaîne !</a>
            </div>
        </div>

        <!-- Quatrième section : Bouton Log -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center">
                <form method="POST">
                    <button type="submit" name="log_data" class="btn btn-secondary">Log</button>
                </form>
                <p class="mt-2">Cliquez sur le bouton pour enregistrer les données dans le fichier <strong>data.json</strong>.</p>
            </div>
        </div>
    </div>

    <!-- 5em section : graphiques -->
    <div class="container mt-5">
        <h2 class="text-center">Graphiques d'évolution</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <canvas id="aboChart"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="viewChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Script pour créer les graphiques -->
    <script>
        // Récupération des données depuis le fichier JSON
        fetch('data.json')
            .then(response => response.json())
            .then(data => {
                const dates = data.map(entry => entry.date);
                const abonnes = data.map(entry => entry.nombre_abonnes);
                const vues = data.map(entry => entry.nombre_de_vues);

                // Création du graphique pour les abonnés
                const aboCtx = document.getElementById('aboChart').getContext('2d');
                const aboChart = new Chart(aboCtx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Nombre d\'abonnés',
                            data: abonnes,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nombre d\'abonnés'
                                }
                            }
                        }
                    }
                });

                // Création du graphique pour les vues
                const viewCtx = document.getElementById('viewChart').getContext('2d');
                const viewChart = new Chart(viewCtx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Nombre de vues',
                            data: vues,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Nombre de vues'
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Erreur lors de la récupération des données :', error));
    </script>

    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-vzYmnEUm92T5CrSu/qEybjcfbtGeNhY9nBAW4GpRmL/f0xkawUmNq9cy+3Ski6Id" crossorigin="anonymous"></script>
</body>
</html>
