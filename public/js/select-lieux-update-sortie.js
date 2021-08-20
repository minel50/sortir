let selectVille = document.getElementById("update_sortie_ville");
let selectLieu = document.getElementById("update_sortie_lieu");
let firstLoad = true;

selectVille.addEventListener("change", updateSelectLieu);

function updateSelectLieu() {
    let idVille = selectVille.value;

    fetch("../../lieu/lieux-par-ville?idVille=" + idVille, {method: 'GET'})
        .then(response => response.json())
        .then(response => {
            let options = "";
            response.map(lieu => {
                options += `<option value="${lieu.id}">${lieu.nom}</option>`

                if (firstLoad) {
                    loadSelectedLieu();
                    firstLoad = false;
                }
            });
            selectLieu.innerHTML = options;
        })
        .catch(e => {
            alert("N'oubliez pas de choisir une ville...");
        });

}

function loadSelectedLieu() {
    let idSortie = document.getElementById('idSortie').value;
    console.log(idSortie);

    fetch("../../lieu/lieu-sortie/" + idSortie, {method: 'GET'})
        .then(response => response.json())
        .then(response => {
            selectLieu.value = response.id;

        })
        .catch(e => {
            alert("Attention, le lieu n'a pas pu être récupéré");
        });
}

//for first loading of the page
updateSelectLieu();