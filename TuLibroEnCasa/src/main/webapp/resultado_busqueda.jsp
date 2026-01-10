<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.List" %>
<%@ page import="libreriavirtual.beans.Libro" %>
<%@ page import="libreriavirtual.beans.Usuario" %>

<%
    Usuario u = (Usuario) session.getAttribute("usuarioObj");
    if (u == null) {
        response.sendRedirect("index.jsp");
        return;
    }

    List<Libro> lista = (List<Libro>) request.getAttribute("listaLibros");
%>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Virtual - Resultados de la Búsqueda</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container resultado app-card">


        <div class="menu-header">
            <h1>Resultados de la Búsqueda</h1>
            <p class="usuario">
                Bienvenido, <strong><%= u.getNombre() %> <%= u.getApellido1() %> <%= u.getApellido2() %></strong>
            </p>
        </div>

        <% if (lista == null || lista.isEmpty()) { %>

            <p>No se encontraron libros.</p>

        <% } else { %>

        <form action="SrvCarrito" method="post">
			<div class="tabla-responsive">
	            <table class="tabla-libros">
	                <thead>
	                    <tr>
	                        <th>Seleccionar</th>
	                        <th>Cantidad</th>
	                        <th>ISBN</th>
	                        <th>Título</th>
	                        <th>Autor</th>
	                        <th>Categoría</th>
	                        <th>Editorial</th>
	                        <th>Precio</th>
	                        <th>Stock</th>
	                    </tr>
	                </thead>
	
	                <tbody>
	                <%
	                    int index = 1;
	                    for (Libro l : lista) {
	                %>
	                    <tr>
	                        <td>
	                            <input type="checkbox" name="libro<%= index %>" value="<%= l.getIsbn() %>">
	                        </td>
	
	                        <td>
	                            <input class="input" type="number" name="cantidad<%= index %>" value="1" min="1" max="<%= l.getStock() %>">
	                        </td>
	
	                        <td><%= l.getIsbn() %></td>
	                        <td><%= l.getTitulo() %></td>
	                        <td><%= l.getNombreAutor() %></td>
	                        <td><%= l.getNombreCategoria() %></td>
	                        <td><%= l.getNombreEditorial() %></td>
	                        <td><%= l.getPrecioUnitario() %> €</td>
	                        <td><%= l.getStock() %></td>
	                    </tr>
	                <%
	                        index++;
	                    }
	                %>
	                </tbody>
	            </table>
			</div>
            <button class="btn-primary" type="submit">Añadir al carrito</button>

        </form>

        <% } %>

    </div>

</body>
</html>
