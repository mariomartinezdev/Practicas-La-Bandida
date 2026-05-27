document.addEventListener("DOMContentLoaded", function () {
  // Asegura que el código se ejecute después de que el DOM esté completamente cargado
  const botonesCategoria = document.querySelectorAll(".btn-categoria"); // Selecciona todos los botones de categoría
  const tarjetas = document.querySelectorAll(
    // Selecciona todas las tarjetas de hamburguesas que tienen un atributo data-categoria
    ".tarjeta_burguer[data-categoria]",
  );
  const titulosCategoria = document.querySelectorAll(
    // Selecciona todos los títulos de categoría que tienen un atributo data-categoria
    ".titulo-categoria[data-categoria]",
  );
  const extrasCategoria = document.querySelectorAll(
    // Selecciona todos los elementos de extras que tienen un atributo data-categoria
    ".extra-categoria[data-categoria]",
  );

  const buscador = document.getElementById("buscar-plato"); // Selecciona el campo de búsqueda por su ID
  const mensajeSinResultados = document.getElementById("sin-resultados"); // Selecciona el mensaje de "sin resultados" por su ID
  const botonVolverArriba = document.getElementById("volver-arriba"); // Selecciona el botón para volver arriba por su ID

  let filtroActivo = "todos"; // Variable para almacenar el filtro de categoría activo, inicialmente se muestra todo

  function normalizarTexto(texto) {
    // Función para normalizar el texto, eliminando acentos y convirtiendo a minúsculas
    return texto
      .toLowerCase()
      .normalize("NFD") // Descompone los caracteres acentuados en su forma base y los acentos como caracteres separados
      .replace(/[\u0300-\u036f]/g, "") // Elimina los acentos
      .trim();
  }

  function actualizarCarta() {
    // Función para actualizar la visualización de las tarjetas según el filtro activo y el texto de búsqueda
    const textoBuscado = buscador ? normalizarTexto(buscador.value) : ""; // Normaliza el texto ingresado en el buscador

    let numeroResultados = 0; // Variable para contar el número de resultados visibles

    tarjetas.forEach(function (tarjeta) {
      // Itera sobre cada tarjeta para determinar si debe mostrarse o no
      const categoriaTarjeta = tarjeta.dataset.categoria; // Obtiene la categoría de la tarjeta desde su atributo data-categoria

      const contenidoTarjeta = normalizarTexto(
        tarjeta.dataset.nombre + " " + tarjeta.textContent, // Combina el nombre de la tarjeta (desde data-nombre) con su contenido textual y lo normaliza
      );

      const coincideCategoria = // Verifica si la tarjeta coincide con el filtro de categoría activo o si el filtro es "todos"
        filtroActivo === "todos" || categoriaTarjeta === filtroActivo;

      const coincideBusqueda = // Verifica si el texto buscado está vacío o si coincide con el contenido de la tarjeta
        textoBuscado === "" || contenidoTarjeta.includes(textoBuscado);

      const debeMostrarse = coincideCategoria && coincideBusqueda; // La tarjeta debe mostrarse si coincide tanto con la categoría como con la búsqueda

      tarjeta.classList.toggle("oculto", !debeMostrarse); // Agrega o quita la clase "oculto" según si la tarjeta debe mostrarse o no

      if (debeMostrarse) {
        // Si la tarjeta se muestra, incrementa el contador de resultados visibles
        numeroResultados++;
      }
    });

    titulosCategoria.forEach(function (titulo) {
      // Itera sobre cada título de categoría para determinar si debe mostrarse o no
      const categoriaTitulo = titulo.dataset.categoria;

      const tieneTarjetasVisibles = Array.from(tarjetas).some(
        // Verifica si hay al menos una tarjeta visible que pertenezca a la categoría del título
        function (tarjeta) {
          // Convierte la NodeList de tarjetas en un array para usar el método some()
          return (
            // La tarjeta pertenece a la categoría del título y no está oculta
            tarjeta.dataset.categoria === categoriaTitulo &&
            !tarjeta.classList.contains("oculto")
          );
        },
      );

      titulo.classList.toggle("oculto", !tieneTarjetasVisibles); // Agrega o quita la clase "oculto" al título según si tiene tarjetas visibles o no
    });

    extrasCategoria.forEach(function (extra) {
      // Itera sobre cada elemento de extras para determinar si debe mostrarse o no
      const categoriaExtra = extra.dataset.categoria; // Obtiene la categoría del elemento de extras desde su atributo data-categoria

      const tieneTarjetasVisibles = Array.from(tarjetas).some(
        // Verifica si hay al menos una tarjeta visible que pertenezca a la categoría del elemento de extras
        function (tarjeta) {
          return (
            tarjeta.dataset.categoria === categoriaExtra &&
            !tarjeta.classList.contains("oculto")
          );
        },
      );

      const mostrarExtra = // El elemento de extras se muestra si tiene tarjetas visibles, el texto buscado está vacío y el filtro activo es "todos" o coincide con la categoría del elemento de extras
        tieneTarjetasVisibles &&
        textoBuscado === "" &&
        (filtroActivo === "todos" || filtroActivo === categoriaExtra);

      extra.classList.toggle("oculto", !mostrarExtra);
    });

    if (mensajeSinResultados) {
      // Si el elemento de mensaje de "sin resultados" existe, muestra o oculta el mensaje según si hay resultados visibles o no
      mensajeSinResultados.classList.toggle("oculto", numeroResultados !== 0);
    }
  }

  botonesCategoria.forEach(function (boton) {
    // Agrega un evento de clic a cada botón de categoría para actualizar el filtro activo y la visualización de las tarjetas
    boton.addEventListener("click", function () {
      // Cuando se hace clic en un botón de categoría, se actualiza el filtro activo con el valor del atributo data-filtro del botón
      filtroActivo = boton.dataset.filtro;

      botonesCategoria.forEach(function (botonMenu) {
        const estaActivo = botonMenu === boton;

        botonMenu.classList.toggle("activo", estaActivo); // Agrega o quita la clase "activo" al botón del menú según si es el botón que se ha clicado o no
        botonMenu.setAttribute("aria-pressed", estaActivo); // Actualiza el atributo aria-pressed para mejorar la accesibilidad, indicando si el botón está activo o no
      });

      actualizarCarta(); // Llama a la función para actualizar la visualización de las tarjetas según el nuevo filtro activo
    });
  });

  if (buscador) {
    // Si el campo de búsqueda existe, agrega un evento de entrada para actualizar la visualización de las tarjetas cada vez que el usuario escriba algo
    buscador.addEventListener("input", actualizarCarta); // Llama a la función para actualizar la visualización de las tarjetas cada vez que el usuario escriba algo en el campo de búsqueda
  }

  if (botonVolverArriba) {
    // Si el botón para volver arriba existe, agrega un evento de scroll para mostrar u ocultar el botón según la posición de desplazamiento y un evento de clic para desplazarse suavemente hacia arriba cuando se haga clic en el botón
    window.addEventListener("scroll", function () {
      // Agrega un evento de scroll para mostrar u ocultar el botón según la posición de desplazamiento
      botonVolverArriba.classList.toggle("oculto", window.scrollY < 400); // Muestra el botón si el usuario ha desplazado hacia abajo más de 400 píxeles, de lo contrario lo oculta
    });

    botonVolverArriba.addEventListener("click", function () {
      // Agrega un evento de clic para desplazarse suavemente hacia arriba cuando se haga clic en el botón
      window.scrollTo({
        // Desplaza la ventana hacia arriba con un comportamiento suave
        top: 0,
        behavior: "smooth",
      });
    });
  }

  actualizarCarta();
});
