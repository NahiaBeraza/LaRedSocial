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
    <title>Librería Virtual - Buscar Libros</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container app-card">

        <div class="menu-header">
            <h1>Buscar Libros</h1>
            <p class="usuario">
                Bienvenido, <strong><%= u.getNombre() %> <%= u.getApellido1() %> <%= u.getApellido2() %></strong>
            </p>
        </div>

        <form class="search-form" action="SrvBuscarLibros" method="post">

            <div class="form-field">
                <label for="titulo">Título</label>
                <input class="input" type="text" id="titulo" name="titulo">
            </div>

            <div class="form-field">
                <label for="autor">Autor</label>
                <input class="input" type="text" id="autor" name="autor">
            </div>

            <div class="form-field">
                <label for="categoria">Categoría</label>
                <select class="input" id="categoria" name="categoria">
                    <option value="cualquiera">Cualquiera</option>
                    <option value="Novela">Novela</option>
                    <option value="Fantasía">Fantasía</option>
                    <option value="Ciencia Ficción">Ciencia Ficción</option>
                    <option value="Clásico">Clásico</option>
                    <option value="Ensayo">Ensayo</option>
                </select>
            </div>

            <div class="form-field">
                <label>Lógica de búsqueda</label>
                <div class="radio-group">
                    <label><input type="radio" name="logica" value="AND"> AND</label>
                    <label><input type="radio" name="logica" value="OR"> OR</label>
                </div>
            </div>

            <div class="form-field">
                <label>Orden</label>
                <div class="radio-group">
                    <label><input type="radio" name="orden" value="titulo"> Título</label>
                    <label><input type="radio" name="orden" value="autor"> Autor</label>
                </div>
            </div>

            <button class="btn-primary" type="submit">Buscar</button>

        </form>

    </div>

</body>

</html>
