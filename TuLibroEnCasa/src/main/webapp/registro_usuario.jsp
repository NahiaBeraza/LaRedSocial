<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Librería Virtual - Registro</title>
<link rel="stylesheet"
	href="<%=request.getContextPath()%>/css/estilos.css">
</head>

<body>
	<%@ include file="nav.jsp" %>
	
	<!-- CONTENEDOR PRINCIPAL -->
	<div id="login-container" class="app-card">

		<!-- PANEL IZQUIERDO -->
		<div id="login-left">
			<h1>Registro</h1>
			<p>Crea tu cuenta para acceder a la Librería Virtual</p>
		</div>

		<!-- PANEL DERECHO -->
		<div id="login-right">
			<h2>Datos del Usuario</h2>

			<form id="login-form" action="SrvRegistrarUsuario" method="post">

				<div class="form-grid">

					<div class="form-field">
						<label>Nombre</label> <input class="input" type="text"
							name="nombre" required>
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
						<label>Dirección</label> <input class="input" type="text"
							name="direccion">
					</div>

					<div class="form-field">
						<label>Fecha de Nacimiento</label> <input class="input"
							type="date" name="fecha_nacimiento">
					</div>

					<div class="form-field">
						<label>Email</label> <input class="input" type="email"
							name="email" required>
					</div>

					<div class="form-field">
						<label>Usuario</label> <input class="input" type="text"
							name="usuario" required>
					</div>

					<div class="form-field">
						<label>Clave</label> <input class="input" type="password"
							name="clave" required>
					</div>

					<div class="form-field">
						<label>Confirmar Clave</label> <input class="input"
							type="password" name="confirmar_clave" required>
					</div>

				</div>

				<button class="btn-primary" type="submit">Alta Usuario</button>

			</form>


			<p class="text-muted" style="margin-top: 20px;">
				¿Ya tienes cuenta? <a href="index.jsp"
					style="color: #6c63ff; font-weight: 600;">Volver al login</a>
			</p>
		</div>

	</div>

</body>

</html>
