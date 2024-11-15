<?php
$ttl = 3600;
$ts = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
header("Expires: $ts");
header("Pragma: cache");
header("Cache-Control: max-age=$ttl");

define('FECHA', 0);
define('ESPECIE', 1);
define('NOMBRE', 2);
define('UBICACION', 3);
define('DESCRIPCION', 4);
define('TELEFONO', 5);
define('FOTO', 6);
define('ESTADO', 7);
define('EN_CASA', 8);
define('FALLECIDO', 9);
define('RECHAZADO', 10);

function transformarUrlImagen($url) {
  preg_match('/id=([^&]+)/', $url, $matches);
  $id = $matches[1] ?? null;

  return $id ? "https://drive.google.com/thumbnail?id={$id}&sz=w1000" : 'https://via.placeholder.com/300x200?text=Sin+foto';
}

$csvFile = 'data.csv';
$data = [];

if (($handle = fopen($csvFile, 'r')) !== false) {
  fgetcsv($handle, 1000, ',');
  while (($row = fgetcsv($handle, 1000, ',')) !== false) {
      $data[] = $row;
  }
  fclose($handle);
}

$ubicaciones = array_unique(array_column($data, UBICACION));
sort($ubicaciones);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="max-image-preview:large">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="Plataforma de Búsqueda de Animales Perdidos durante la DANA de 2024" />
    <meta property="og:description" content="Ayudemos a reunir a las animales perdidos con sus familias!" />
    <meta property="og:image" content="https://gorogoro.es/wp-content/uploads/2020/08/gorocat.png">
    <meta property="og:image:secure_url" content="https://gorogoro.es/wp-content/uploads/2020/08/gorocat.png">
    <meta property="og:url" content="https://gorogoro.es/dana/" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="gorogoro: purr &amp; roll | Plataforma de Búsqueda de Animales Perdidos durante la DANA de 2024">
    <link rel="canonical" href="https://gorogoro.es/dana">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Plataforma de Búsqueda de Animales Perdidos durante la DANA de 2024" />
    <meta name="twitter:description" content="Ayudemos a reunir a las animales perdidos con sus familias!" />
    <meta name="twitter:image" content="https://gorogoro.es/wp-content/uploads/2020/08/gorocat.png">


    <title>🐾 Plataforma de Búsqueda de Animales Perdidos durante la DANA de 2024</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; color: #333; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        h1 { color: #ff6f61; }
        .container { max-width: 1200px; width: 100%; }
        .search-box { width: 100%; margin-bottom: 20px; display: flex; gap: 10px; justify-content: space-between}
        .search-box input, .search-box select { padding: 10px; font-size: 1em; border-radius: 5px; border: 1px solid #ccc; }
        .search-box input, .search-box select { flex: 1; }
        .cards { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .card { display: flex; flex-direction: row; align-items: center; background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); padding: 20px; max-width: 600px; width: 100%; transition: transform 0.3s; overflow: hidden; position: relative; }
        .card:hover { transform: translateY(-5px); }
        .card.hidden{ display: none; }
        .card img { width: 200px; height: 200px; object-fit: cover; border-radius: 8px; margin-right: 20px; }
        .card-content { flex: 1; width: 100% }
        .card-content h3 { font-size: 1.5em; color: #333; margin: 10px 0; }
        .card-content p { color: #555; margin: 5px 0; }
        .info, .contact { margin: 5px 0; }
        .contact a { font-weight: bold; color: #ff6f61; font-size: 1.2em; text-decoration: none; }
        .contact a:hover { text-decoration: underline; }
        .btn { display: inline-block; font-size: 1em; padding: 15px 15px; background-color: #ff6f61; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background-color 0.3s; border: 0}
        .share-btn { display: inline-block; padding: 10px 15px; margin-top: 10px; background-color: #ff6f61; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background-color 0.3s; }
        .share-btn:hover { background-color: #ff3f3f; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #666; text-align: center }
        @media (max-width: 768px) {
            .card { flex-direction: column; align-items: flex-start; }
            .card img { width: 100%; height: auto; margin: 0 0 5px; }
            .card img em { width: 100%; margin: 5px 0 15px; }
            .search-box { flex-direction: column; }
            .search-box select { width: 100%; }
            .img-container em { text-align: center; }
        }
        .active-filters { margin-top: 10px; font-style: italic; color: #333; }
        .badge { display: inline-block; padding: 5px 10px; font-size: 0.5em; font-weight: bold; border-radius: 12px; color: #fff; margin-left: 10px; float: right; }
        .badge-found { background-color: dodgerblue; }
        .badge-dead { background-color: black; }
        .badge-home { background-color: darkolivegreen; }
        .badge-rejected { background-color: orange; }
        .badge-lost { background-color: crimson; }
        a { color: #ff3f3f; text-decoration: none }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0, 0, 0, 0.8); }
        .modal-content { margin: auto; display: block; width: 100%; height: 100%; object-fit: contain; }
        .close { position: absolute; top: 10px; right: 25px; color: #fff; font-size: 35px; font-weight: bold; cursor: pointer; }
        .img-container { display: flex; flex-direction: column; }
        .img-container em { text-align: center; margin-top: 5px; margin-right: 15px; }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const anchor = window.location.hash;
            if (anchor) {
                const targetCard = document.querySelector(anchor);

                if (targetCard) {
                    targetCard.scrollIntoView({ behavior: "smooth", block: "center" });
                    document.querySelectorAll(".card").forEach((card) => {
                        if (card !== targetCard) {
                            card.classList.add("hidden");
                        }
                    });
                }
            }
        });

        function shareCard(anchor) {
            const url = `${window.location.origin}${window.location.pathname}#${anchor}`;
            if (navigator.share) {
                navigator.share({ title: document.title, url: url })
                    .then(() => console.log("Shared successfully"))
                    .catch(console.error);
            } else {
                prompt("Copia el enlace para compartir:", url);
            }
        }

        function stripAccents(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, '');
        }

        function filterCards() {
            const searchValue = stripAccents(document.getElementById('search').value).toLowerCase();
            const locationValue = stripAccents(document.getElementById('location').value);
            const speciesValue = stripAccents(document.getElementById('species').value);
            const statusValue = stripAccents(document.getElementById('status').value);
            const cards = document.querySelectorAll('.card');
            let totalCards = cards.length;

            let activeFilters = [];

            cards.forEach(card => {
                const location = stripAccents(card.getAttribute('data-location')).toLowerCase();
                const name = stripAccents(card.getAttribute('data-name')).toLowerCase();
                const description = stripAccents(card.getAttribute('data-description')).toLowerCase();
                const species = card.getAttribute('data-species');
                const status = card.getAttribute('data-status');

                if ((location.includes(searchValue) || description.includes(searchValue) || name.includes(searchValue) ) &&
                    (locationValue === "" || location === locationValue.toLowerCase()) &&
                    (speciesValue === "" || species === speciesValue) &&
                    (statusValue === "" || status === statusValue)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                    totalCards--;
                }
            });

            document.getElementById('counter').innerHTML = totalCards.toString();

            if (searchValue) activeFilters.push(`Descripción: "${searchValue}"`);
            if (locationValue) activeFilters.push(`Ubicación: "${locationValue}"`);
            if (speciesValue) activeFilters.push(`Especie: "${speciesValue}"`);
            if (statusValue) activeFilters.push(`Estado: "${statusValue}"`);

            document.getElementById('activeFilters').innerHTML = activeFilters.length > 0 ?
                `<p>Filtros activos:</p><ul><li> ${activeFilters.join('</li><li>')} </li></ul>` : '';

            document.getElementById('noResults').style.display = totalCards === 0 ? 'block' : 'none';
        }
        function clearFilters() {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
            document.getElementById('activeFilters').textContent = '';
            document.getElementById('search').value = '';
            document.getElementById('species').value = '';
            document.getElementById('status').value = '';
            document.getElementById('location').value = '';
            filterCards();
        }
        function openModal(imgSrc) {
            const modal = document.getElementById("myModal");
            const modalImg = document.getElementById("img01");
            const closeBtn = document.getElementsByClassName("close")[0];

            modal.style.display = "block";
            modalImg.src = imgSrc;

            // Cerrar la modal al hacer clic en la "X"
            closeBtn.onclick = function() {
                modal.style.display = "none";
            }

            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape") { // Verifica si se presionó la tecla ESC
                    modal.style.display = "none"; // Cierra el modal
                }
            });

            modalImg.onclick = function() {
                modal.style.display = "none";
            }

            // Cerrar la modal al hacer clic fuera de la imagen
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h1>🐾 Plataforma de Búsqueda de Animales Perdidos Durante la DANA de 2024</h1>
    <p>Si has rescatado un animal perdido o estás buscando un animal desaparecido durante la DANA, haz click aquí:</p>
    <p style="text-align: center;"><a class="share-btn" target="_blank" href="https://forms.gle/V2ncxjUDJ2Vdwxzg7">📝 NOTIFICAR ANIMALES PERDIDOS O ENCONTRADOS</a></p>
    <p><strong>Debido a la validación manual que hacemos de los formularios, el proceso de alta no es inmediato.</strong> Si tienes alguna duda <a target="_blank" href="mailto:gorogoro.purr.roll@gmail.com">contacta con nosotros</a>.</p>
    <p>Ayudemos a reunir a las animales perdidos con sus familias!</p>

    <!-- Buscador -->
    <div class="search-box">
        <input type="text" id="search" onkeyup="filterCards()" placeholder="Buscar por descripción">
        <select id="location" onchange="filterCards()">
            <option value="">Todas las ubicaciones</option>
          <?php foreach ($ubicaciones as $ubicacion): ?>
              <option value="<?php echo htmlentities($ubicacion); ?>"><?php echo htmlentities($ubicacion); ?></option>
          <?php endforeach; ?>
        </select>
        <select id="species" onchange="filterCards()">
            <option value="">Todas las especies</option>
            <option value="Perro/Gos">Perro/Gos</option>
            <option value="Gato/Gat">Gato/Gat</option>
            <option value="Otros/Altres">Otros/Altres</option>
        </select>
        <select id="status" onchange="filterCards()">
            <option value="">Todos los estados</option>
            <option value="He encontrado/He trobat">Rescatado</option>
            <option value="Busco/Busque">Desaparecido</option>
            <option value="En Casa">En casa</option>
            <option value="Fallecido">Fallecido</option>
            <option value="Rechazado">Busca nueva familia</option>
        </select>
        <button class="btn" onclick="clearFilters()">🧹 Limpiar filtros</button>
    </div>
    <div id="ficha-counter">
        <strong>Total de registros: </strong><span id="counter"><?php echo count($data); ?></span>
        <span id="filter-info"></span>
    </div>
    <p id="activeFilters" class="active-filters"></p>
    <div class="cards">
      <?php foreach ($data as $index => $item):
        $imgUrl = htmlentities(transformarUrlImagen($item[FOTO] ?? ''));
        $nombre = htmlentities($item[NOMBRE] ?? '');
        $especie = htmlentities($item[ESPECIE] ?? '');
        $estado = htmlentities($item[ESTADO] ?? '');
        $badgeText = ($estado === "He encontrado/He trobat") ? "🛟 Rescatado" : "🔍 Desaparecido";
        $badgeClass = ($estado === "He encontrado/He trobat") ? "badge-found" : "badge-lost";

        if ($item[FALLECIDO] == 'TRUE') {
          $estado = 'Fallecido';
          $badgeText = '💀 ' . $estado;
          $badgeClass = 'badge-dead';
        }

        if ( $item[EN_CASA] == 'TRUE') {
          $estado = 'En Casa';
          $badgeText = '🎉 ' . $estado;
          $badgeClass = 'badge-home';
        }

        if ( $item[RECHAZADO] == 'TRUE') {
          $estado = 'Rechazado';
          $badgeText = '☀️  Busca nueva familia ';
          $badgeClass = 'badge-rejected';
        }

        $hash = md5($nombre.$especie.$estado.$imgUrl);
        $anchor = 'card-' . $hash; // Anchor unique identifier for each card

        $locationText = ($estado === "He encontrado/He trobat") ? "Encontrado en" : "Última vez visto en";
        ?>
          <div class="card" id="<?php echo $anchor; ?>"
               data-location="<?php echo htmlentities($item[UBICACION] ?? ''); ?>"
               data-description="<?php echo htmlentities($item[DESCRIPCION] ?? ''); ?>"
               data-species="<?php echo $especie; ?>"
               data-status="<?php echo $estado; ?>"
               data-name="<?php echo $nombre; ?>"
               data-fallecido="<?php echo $estado == 'Fallecido' ? 1 : 0; ?>"
               data-rechazado="<?php echo $estado == 'Rechazado' ? 1 : 0; ?>"
               data-en-casa="<?php echo $estado == 'En Casa' ? 1 : 0; ?>"
          >
              <div class="img-container">
                  <img src="<?php echo $imgUrl; ?>" alt="Foto de <?php echo htmlentities($item[NOMBRE] ?? ''); ?>" onclick="openModal('<?php echo $imgUrl; ?>')" style="cursor: pointer;">
                  <em>🔍 Click para ampliar</em>
              </div>
              <div class="card-content">
                  <h3>
                    <?php echo $nombre; ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                  </h3>
                  <p class="info"><strong>Fecha:</strong> <?php echo htmlentities($item[FECHA] ?? ''); ?></p>
                  <p class="info"><strong>Especie:</strong> <?php echo $especie; ?></p>
                  <p class="info"><strong><?php echo $locationText; ?>:</strong> <?php echo htmlentities($item[UBICACION] ?? ''); ?></p>
                  <p><strong>Descripción:</strong> <?php echo htmlentities($item[DESCRIPCION] ?? ''); ?></p>
                  <p class="contact">📞 <a href="tel:<?php echo htmlentities($item[TELEFONO] ?? ''); ?>"><?php echo htmlentities($item[TELEFONO] ?? ''); ?></a></p>
                  <a href="javascript:void(0);" class="share-btn" onclick="shareCard('<?php echo $anchor; ?>')">🔗 Compartir</a>
              </div>
          </div>
      <?php endforeach; ?>
    </div>

    <div id="noResults" style="display:none; color: red; text-align: center;">
        <p>No se encontraron resultados para los filtros seleccionados.</p>
    </div>

    <div id="myModal" class="modal">
        <span class="close">&times;</span>
        <img class="modal-content" id="img01">
    </div>

    <div class="footer">
        <p>Hecho con ❤️ por <strong><a href="https://gorogoro.es">gorogoro: purr & roll</a></strong></p> 
        <p>Contacto: <a target="_blank" href="mailto:gorogoro.purr.roll@gmail.com">Email</a> | <a target="_blank" href="https://instagram.com/gorogoro.purr.roll">Instagram</a></p>
        <p>Casos centralizados de <strong><a target="_blank" href="https://instagram.com/perros_desaparecido_riada_2024">@perros_desaparecido_riada_2024</a></strong>, <strong><a target="_blank" href="https://www.instagram.com/animales_dana_valencia">@animales_dana_valencia</a></strong> y <strong><a target="_blank" href="https://www.instagram.com/animalesafectadosporladana">@animalesafectadosporladana</a></strong></p>
    </div>
</div>
</body>
</html>
