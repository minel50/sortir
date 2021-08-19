let selectVille = document.getElementById("sortie_ville");
let selectLieu = document.getElementById("sortie_lieu");

selectVille.addEventListener("change", updateSelectLieu);

function updateSelectLieu() {
    let idVille = selectVille.value;

    fetch("../lieu/lieux-par-ville?idVille=" + idVille, {method: 'GET'})
        .then(response => response.json())
        .then(response => {
            let options = "";
            response.map(lieu => {
                options += `<option value="${lieu.id}">${lieu.nom}</option>`
            });
            selectLieu.innerHTML = options;
        })
        .catch(e => {
            alert("N'oubliez pas de choisir une ville...");
        });

}