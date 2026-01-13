<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="libreriavirtual.beans.Usuario" %>

<%
    Usuario u = (Usuario) session.getAttribute("usuarioObj");

    if (u == null) {
        response.sendRedirect("index.jsp");
        return;
    }
%>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Virtual - Compra Finalizada</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container final app-card">

        <h1>¡Compra realizada con éxito!</h1>

        <p class="usuario">
            Gracias por su compra, 
            <strong><%= u.getNombre() %> <%= u.getApellido1() %></strong>.
        </p>

        <p>Su pedido ha sido confirmado y está siendo procesado.</p>

        <p>
            Número de pedido: 
            <strong><%= session.getAttribute("idPedido") %></strong>
        </p>

        <button class="btn-primary" onclick="location.href='buscar_libros.jsp'">
            Volver a la tienda
        </button>

    </div>

</body>

</html>
