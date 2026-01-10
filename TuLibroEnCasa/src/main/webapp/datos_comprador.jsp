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
<title>Librería Virtual - Datos del Comprador</title>
<link rel="stylesheet"
	href="<%=request.getContextPath()%>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
	<div class="menu-container comprador app-card">

		<div class="menu-header">
			<h1>Resumen del Carrito</h1>
			<p class="usuario">
				Bienvenido, <strong><%=u.getNombre()%> <%=u.getApellido1()%>
					<%=u.getApellido2()%></strong>
			</p>
		</div>

		<%
		if (carrito == null || carrito.estaVacio()) {
		%>

		<p>No hay libros en el carrito.</p>
		<button class="btn-primary"
			onclick="location.href='buscar_libros.jsp'">Seleccionar
			libros</button>

		<%
		} else {
		%>
		<div class="tabla-responsive">
			<table class="tabla-libros">
				<thead>
					<tr>
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
					for (LineaCarrito lc : carrito.getLineas()) {
						Libro l = lc.getLibro();
					%>
					<tr>
						<td><%=lc.getCantidad()%></td>
						<td><%=l.getIsbn()%></td>
						<td><%=l.getTitulo()%></td>
						<td><%=l.getNombreAutor()%></td>
						<td><%=l.getNombreCategoria()%></td>
						<td><%=l.getNombreEditorial()%></td>
						<td><%=String.format("%.2f", l.getPrecioUnitario())%> €</td>
						<td><%=String.format("%.2f", lc.getSubtotal())%> €</td>
					</tr>
					<%
					}
					%>
				</tbody>
			</table>
		</div>
		<p>
			<strong>TOTAL COMPRA: <%=String.format("%.2f", carrito.getTotal())%>
				€
			</strong>
		</p>

		<h2>Datos del Comprador</h2>

		<form class="comprador-form" action="SrvPedido" method="post">

			<div class="form-field">
				<label>Nombre</label> <input class="input" type="text" name="nombre"
					required>
			</div>

			<div class="form-field">
				<label>Primer Apellido</label> <input class="input" type="text"
					name="apellido1" required>
			</div>

			<div class="form-field">
				<label>Segundo Apellido</label> <input class="input" type="text"
					name="apellido2">
			</div>

			<div class="form-field">
				<label>DNI</label> <input class="input" type="text" name="dni"
					required>
			</div>

			<div class="form-field">
				<label>Tarjeta de Crédito</label> <input class="input" type="text"
					name="tarjeta" required>
			</div>

			<div class="comprador-buttons">
				<button class="btn-primary btn-success" type="submit">Realizar
					compra real</button>

				<button class="btn-primary" type="button"
					onclick="location.href='estado_carrito.jsp'">Volver atrás</button>
				<button class="btn-primary" type="button"
					onclick="location.href='buscar_libros.jsp'">Seleccionar
					más libros</button>
			</div>

		</form>

		<%
		}
		%>

	</div>

</body>
</html>
