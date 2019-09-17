// Obtencion Ranking Ganadores de un Grand Slam especifico //

const sid = "1GZu4w8_NiJS8I1--C-N5O2dPoj_Bv-ojekMRDS2ToMQ";

const getTourneyBySlug = (tourney_slug, top) => {
    return new Promise(function(resolve) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200)
                resolve(decodeTourneyRanking(this.responseText, tourney_slug));
        };
        xmlhttp.open("GET", `slam/${tourney_slug}/top/${top}/sid/${sid}`, true);
        xmlhttp.send();
    });
}

const decodeTourneyRanking = (message_json, tourney_slug) => {
    let rsp = '';
    let iRow = 1;
    let message = JSON.parse(message_json);
    for (key in message) {
        rsp += `<tr><td>${iRow}</td><td><a class='player_${tourney_slug}' href="slam/${tourney_slug}/player/${key}/sid/${sid}">${message[key].nombre}</a></td><td>${message[key].titulos}</td></tr>`;
        iRow++;
    }

    return rsp;
}

const refreshTourneyRankingBySlug = (tourney_slug) => {
    showCoverCard(tourney_slug);
    hideTourneyTitles(tourney_slug);
    let cbo = document.getElementById(`card_${tourney_slug}`).getElementsByTagName("select")[0];
    let top = cbo.options[cbo.selectedIndex].value;
    getTourneyBySlug(tourney_slug, top).then(response => {
        document.getElementById(`card_${tourney_slug}`).getElementsByTagName("tbody")[0].innerHTML = response;
        hideCoverCard(tourney_slug);
    });
}

/////////////////////////////////////////////////////////////
// Obtencion Titulos Ganados por un Jugador especifico para un Grand Slam especifico //

const getTourneyTitlesByPlayerSlug = (url) => {
    return new Promise(function(resolve) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200)
                resolve(decodeTourneyTitles(this.responseText));
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    });
}

const decodeTourneyTitles = (message_json) => {
        let rsp = "<ul>";
        let message = JSON.parse(message_json);
        for (key in message)
            rsp += `<li>${(rsp == "<ul>" ? `<b>${message[key]}</b>` : `${message[key]}`)}</li>`;

    return rsp += '</ul>';
}

const showTourneyTitles = (tourney_slug) => {
    let div_titles = document.getElementById(`card_${tourney_slug}`).getElementsByClassName("titles");
    if (div_titles.length == 0) return;
    div_titles[0].classList.add("full");
}

const hideTourneyTitles = (tourney_slug) => {
    let div_titles = document.getElementById(`card_${tourney_slug}`).getElementsByClassName("titles full");
    if (div_titles.length == 0) return;
    div_titles[0].getElementsByTagName("ul")[0].innerHTML = "";
    div_titles[0].classList.remove("full");
}

/////////////////////////////////////////////////////////////
// Spinner //

const showCoverCard = (tourney_slug) => {
    let cover = document.getElementById(`card_${tourney_slug}`).getElementsByClassName("cover");
    if (cover.length == 0) return;
    cover[0].classList.add("show");
}

const hideCoverCard = (tourney_slug) => {
    let cover = document.getElementById(`card_${tourney_slug}`).getElementsByClassName("cover show");
    if (cover.length == 0) return;
    cover[0].classList.remove("show");
}

/////////////////////////////////////////////////////////////
// Carga Inicial //

function init_load() {
    var slams = ['wimbledon', 'us-open', 'australian-open', 'roland-garros'];
    slams.forEach(slam => {
        getTourneyBySlug(slam, 3)
            .then(response => {
                document.getElementById(`card_${slam}`).getElementsByTagName("tbody")[0].innerHTML = response;
                hideCoverCard(slam);
            });
    });
}

/////////////////////////////////////////////////////////////
// Handler Link Jugador ??

document.addEventListener('click', function(e) {
    e.preventDefault();
    if (e.target && e.target.className.indexOf('player_') > -1) {
        let tourney_slug = e.target.className.substring(e.target.className.indexOf('_') + 1);
        hideTourneyTitles(tourney_slug);
        showCoverCard(tourney_slug);
        getTourneyTitlesByPlayerSlug(e.target.href)
            .then(response => {
                document.getElementById(`card_${tourney_slug}`).getElementsByClassName("titles")[0].getElementsByTagName("ul")[0].innerHTML += response;
                hideCoverCard(tourney_slug);
                showTourneyTitles(tourney_slug);
            });
    }
});

/////////////////////////////////////////////////////////////

window.onload = function() {
    init_load();
};