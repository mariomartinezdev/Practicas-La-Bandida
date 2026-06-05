document.addEventListener("DOMContentLoaded", function () {
  const buscador = document.getElementById("buscar-plato");
  const tarjetas = Array.from(document.querySelectorAll(".tarjeta_burguer"));
  const titulos = Array.from(document.querySelectorAll(".titulo-categoria"));
  const extras = document.querySelector(".extra-categoria");
  const mensajeSinResultados = document.getElementById("sin-resultados");
  const botonVolverArriba = document.getElementById("volver-arriba");

  function normalizarTexto(texto) {
    return texto
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();
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
      const contenidoBuscable = normalizarTexto(nombre + " " + ingredientes);

      const coincideBusqueda = palabrasBuscadas.every(function (palabra) {

		let palabras = contenidoBuscable.split(/[ ,]+/);

		for(let p of palabras){

			if(p.slice(0,palabra.length)===palabra){return true;}
	
		}

		return false;
		
      });

      const mostrarTarjeta = busqueda === "" || coincideBusqueda;

	  if(!mostrarTarjeta){
		  tarjeta.className="oculto";
	  }else{
          tarjeta.className="tarjeta_burguer";
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
        busqueda === "" || numeroResultados !== 0,
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
        behavior: "smooth",
      });
    });

    controlarBotonVolverArriba();
  }
});
