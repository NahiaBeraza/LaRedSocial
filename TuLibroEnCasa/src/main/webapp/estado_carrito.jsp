<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ page import="libreriavirtual.beans.Usuario"%>
<%@ page import="libreriavirtual.beans.Carrito"%>
<%@ page import="libreriavirtual.beans.LineaCarrito"%>
<%@ page import="libreriavirtual.beans.Libro"%>

<%
Usuario u = (Usuario) session.getAttribute("usuarioObj");
if (u == null) {
    response.sendRedirect("index.jsp");
    return;
}

Carrito carrito = (Carrito) session.getAttribute("carrito");
%>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Virtual - Estado del Carrito</title>
    <link rel="stylesheet" href="<%= request.getContextPath() %>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
    <div class="menu-container carrito app-card">

        <div class="menu-header">
            <h1>Estado del Carrito</h1>
            <p class="usuario">
                Bienvenido, <strong><%= u.getNombre() %> <%= u.getApellido1() %> <%= u.getApellido2() %></strong>
            </p>
        </div>

        <% if (carrito == null || carrito.estaVacio()) { %>

            <p>No hay libros en el carrito.</p>
            <button class="btn-primary" onclick="location.href='buscar_libros.jsp'">Seleccionar libros</button>

        <% } else { %>

        <form action="SrvActualizarCarrito" method="post">
			<div class="tabla-responsive">
	            <table class="tabla-libros">
	                <thead>
	                    <tr>
	                        <th style="color: white;">Eliminar</th>
	                        <th>Cantidad</th>
	                        <th>ISBN</th>
	                        <th>Título</th>
	                        <th>Autor</th>
	                        <th>Categoría</th>
	                        <th>Editorial</th>
	                        <th>Precio Unidad</th>
	                        <th>Precio Total</th>
	                    </tr>
	                </thead>
	
	                <tbody>
	                <%
	                    int index = 1;
	                    for (LineaCarrito lc : carrito.getLineas()) {
	                        Libro l = lc.getLibro();
	                %>
	                    <tr>
	                        <td>
	                            <input type="checkbox" name="eliminar<%=index%>" value="<%=l.getIsbn()%>">
	                        </td>
	
	                        <td>
	                            <input class="input" type="number" name="cantidad<%=index%>"
	                                   value="<%=lc.getCantidad()%>" min="1" max="<%=l.getStock()%>">
	                        </td>
	
	                        <td><%=l.getIsbn()%></td>
	                        <td><%=l.getTitulo()%></td>
	                        <td><%=l.getNombreAutor()%></td>
	                        <td><%=l.getNombreCategoria()%></td>
	                        <td><%=l.getNombreEditorial()%></td>
	                        <td><%= String.format("%.2f", l.getPrecioUnitario()) %> €</td>
	                        <td><%= String.format("%.2f", lc.getSubtotal()) %> €</td>
	                    </tr>
	                <%
	                        index++;
	                    }
	                %>
	                </tbody>
	            </table>	
			</div>
            <p><strong>TOTAL COMPRA: <%= String.format("%.2f", carrito.getTotal()) %> €</strong></p>

            <div class="carrito-buttons">
                <button class="btn-primary" type="submit">Actualizar carrito</button>
                <button class="btn-primary btn-success" type="button" onclick="location.href='datos_comprador.jsp'">Realizar compra</button>

                <button class="btn-primary" type="button" onclick="location.href='buscar_libros.jsp'">Seleccionar más libros</button>
            </div>

        </form>

        <% } %>

    </div>

</body>
</html>
