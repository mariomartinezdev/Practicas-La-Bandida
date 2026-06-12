document.addEventListener("DOMContentLoaded", function () {
  const buscador = document.getElementById("buscar-plato");
  const tarjetas = Array.from(document.querySelectorAll(".tarjeta_burguer"));
  const titulos = Array.from(document.querySelectorAll(".titulo-categoria"));
  const extras = document.querySelector(".extra-categoria");
  const mensajeSinResultados = document.getElementById("sin-resultados");
  const botonVolverArriba = document.getElementById("volver-arriba");

  function normalizarTexto(texto) {
    return String(texto)
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();
  }

  function normalizarPrecio(precio) {
    const precioLimpio = String(precio)
      .toLowerCase()
      .replace(/€/g, "")
      .replace(/\s+/g, "")
      .replace(/,/g, ".")
      .replace(/[^0-9.]/g, "");

    if (precioLimpio === "") {
      return "";
    }

    const numero = Number(precioLimpio);

    if (Number.isNaN(numero)) {
      return precioLimpio;
    }

    return [
      precioLimpio,
      numero.toString(),
      numero.toFixed(2),
      numero.toFixed(2).replace(".", ",")
    ].join(" ");
  }

  function actualizarBusqueda() {
    if (!buscador) {
      return;
    }

    const busqueda = normalizarTexto(buscador.value);
    const palabrasBuscadas = busqueda.split(/\s+/).filter(Boolean);

    let numeroResultados = 0;

    tarjetas.forEach(function (tarjeta) {
      const nombre = tarjeta.dataset.nombre || "";
      const ingredientes = tarjeta.dataset.ingredientes || "";
      const precio = tarjeta.dataset.precio || "";

      const contenidoBuscable = normalizarTexto(
        nombre + " " + ingredientes + " " + precio
      );
      const precioBuscable = normalizarTexto(normalizarPrecio(precio));

      const coincideBusqueda = palabrasBuscadas.every(function (palabra) {
        const palabraComoPrecio = normalizarTexto(normalizarPrecio(palabra));

        return (
          contenidoBuscable.includes(palabra) ||
          (palabraComoPrecio !== "" && precioBuscable.includes(palabraComoPrecio))
        );
      });

      const mostrarTarjeta = busqueda === "" || coincideBusqueda;

      tarjeta.classList.toggle("oculto", !mostrarTarjeta);

      if (mostrarTarjeta) {
        numeroResultados++;
      }
    });

    titulos.forEach(function (titulo) {
      const categoria = titulo.dataset.categoria;

      const tieneTarjetasVisibles = tarjetas.some(function (tarjeta) {
        return (
          tarjeta.dataset.categoria === categoria &&
          !tarjeta.classList.contains("oculto")
        );
      });

      titulo.classList.toggle("oculto", !tieneTarjetasVisibles);
    });

    if (extras) {
      extras.classList.toggle("oculto", busqueda !== "");
    }

    if (mensajeSinResultados) {
      mensajeSinResultados.classList.toggle(
        "oculto",
        busqueda === "" || numeroResultados !== 0
      );
    }
  }

  function controlarBotonVolverArriba() {
    if (!botonVolverArriba) {
      return;
    }

    if (window.scrollY >= 200) {
      botonVolverArriba.classList.remove("oculto");
    } else {
      botonVolverArriba.classList.add("oculto");
    }
  }

  if (buscador) {
    buscador.addEventListener("input", actualizarBusqueda);
    actualizarBusqueda();
  }

  if (botonVolverArriba) {
    window.addEventListener("scroll", controlarBotonVolverArriba);

    botonVolverArriba.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });

    controlarBotonVolverArriba();
  }
});
