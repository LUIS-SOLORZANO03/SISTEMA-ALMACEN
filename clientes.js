document.addEventListener("DOMContentLoaded", () => {
  const tipoPlan = document.getElementById("tipo_plan");
  const contDetalle = document.getElementById("detalle_plan_container");

  const planes = {
    Internet: [
      "200 Mbps S/50","300 Mbps S/60","400 Mbps S/70","500 Mbps S/80",
      "600 Mbps S/90","700 Mbps S/100","800 Mbps S/110"
    ],
    DUO: [
      "150 Mbps S/75","200 Mbps S/85","300 Mbps S/95","400 Mbps S/105",
      "500 Mbps S/115","600 Mbps S/125","700 Mbps S/135"
    ],
    "Cable TV": ["Plan Básico","Plan Familiar","Plan Premium"]
  };

  tipoPlan?.addEventListener("change", () => {
    const sel = tipoPlan.value;
    contDetalle.innerHTML = "";

    if (!planes[sel]) return;

    const wrapper = document.createElement("div");
    wrapper.className = "col-md-12";

    wrapper.innerHTML = `
      <label class="form-label">Seleccione Plan Detallado</label>
      <select name="plan_detalle" class="form-select" required>
        <option value="">Seleccione</option>
        ${planes[sel].map(opt => `<option value="${opt}">${opt}</option>`).join("")}
      </select>
    `;
    contDetalle.appendChild(wrapper);
  });
});

/* ========================
   Gestión de Materiales
======================== */
const materiales = [
  "Módem","Conectores RG6","Conectores de fibra",
  "Cable coaxial 20m","Cable coaxial 10m","Cable coaxial 5m",
  "Fibra 100m","Fibra 150m","Fibra 220m",
  "Cinta aislante","Grapas","Pigtail"
];

function abrirMateriales(btn) {
  const idCliente = btn.getAttribute("data-cliente");
  document.getElementById("id_cliente_materiales").value = idCliente;

  const cont = document.getElementById("materialesBody");
  cont.innerHTML = "";

  materiales.forEach(mat => {
    const hash = simpleHash(mat);
    const div = document.createElement("div");
    div.className = "col-md-6 mb-2";
    div.innerHTML = `
      <div class="form-check">
        <input class="form-check-input mat-check" type="checkbox" id="chk_${hash}" value="${mat}" name="materiales[]">
        <label class="form-check-label" for="chk_${hash}">${mat}</label>
      </div>
      <input type="number" name="cantidad_${hash}" class="form-control mt-1 mat-cant" placeholder="Cantidad" min="1" disabled>
      <button type="button" class="btn btn-sm btn-outline-secondary mt-1 d-none btn-sermod" data-hash="${hash}">
        Serie/Modelo Módem
      </button>
    `;
    cont.appendChild(div);
  });

  cont.querySelectorAll(".mat-check").forEach(chk => {
    chk.addEventListener("change", () => {
      const parent = chk.closest(".col-md-6");
      const inputCant = parent.querySelector(".mat-cant");
      const btnSerMod = parent.querySelector(".btn-sermod");

      inputCant.disabled = !chk.checked;
      btnSerMod.classList.toggle("d-none", !(chk.checked && chk.value === "Módem"));
    });
  });
}

/* ========================
   Envío de formulario
======================== */
document.getElementById("formMateriales")?.addEventListener("submit", async function(e) {
  e.preventDefault();
  const data = new FormData(this);

  // Si seleccionó "Módem", pedimos serie/modelo
  if (data.getAll("materiales[]").includes("Módem") && !data.get("serie_modem")) {
    const { value: details } = await Swal.fire({
      title: "Detalles del Módem",
      html: `
        <input id="swal-serie" class="swal2-input" placeholder="Serie" required>
        <input id="swal-modelo" class="swal2-input" placeholder="Modelo" required>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: "Guardar",
      preConfirm: () => {
        const serie = document.getElementById("swal-serie").value.trim();
        const modelo = document.getElementById("swal-modelo").value.trim();
        if (!serie || !modelo) {
          Swal.showValidationMessage("Debe ingresar serie y modelo");
          return false;
        }
        return { serie, modelo };
      }
    });

    if (!details) return; // canceló
    data.append("serie_modem", details.serie);
    data.append("modelo_modem", details.modelo);
  }

  try {
    const res = await fetch("registrar_materiales.php", { method: "POST", body: data });
    if (!res.ok) throw new Error("Error en servidor");
    const modal = bootstrap.Modal.getInstance(document.getElementById("materialesModal"));

    Swal.fire({
      icon: "success",
      title: "¡Materiales registrados!",
      timer: 2000,
      showConfirmButton: false
    });
    modal?.hide();
    this.reset();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err.message });
  }
});

/* ========================
   Hash simple para IDs
======================== */
function simpleHash(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = (hash << 5) - hash + str.charCodeAt(i);
    hash |= 0; // Convierte a 32 bits
  }
  return Math.abs(hash);
}
