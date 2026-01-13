<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en la Compra</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container error app-card">

        <h1>Ha ocurrido un error</h1>

        <p>
            Lo sentimos, pero no hemos podido procesar su pedido en este momento.
        </p>

        <p>
            Por favor, inténtelo de nuevo más tarde.
        </p>

        <button class="btn-primary" onclick="location.href='buscar_libros.jsp'">
            Volver a la tienda
        </button>

    </div>

</body>

</html>
