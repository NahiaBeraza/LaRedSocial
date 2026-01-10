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
    <title>Librería Virtual - Menú</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container app-card">

        <div class="menu-header">
            <h1>Librería Virtual</h1>
            <p class="usuario">
                Bienvenido, <strong><%= u.getNombre() %> <%= u.getApellido1() %> <%= u.getApellido2() %></strong>
            </p>
        </div>

        <div class="menu-buttons">
            <button class="btn-primary" onclick="location.href='buscar_libros.jsp'">Comprar Libros</button>
            <button class="btn-primary">Foro (próximamente)</button>
            <button class="btn-primary">Modificar Datos (próximamente)</button>
        </div>

    </div>

</body>

</html>
