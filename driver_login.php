            // Update driver's online status with debug logging
            try {
                $stmt = $pdo->prepare("UPDATE driver SET is_online = 'online' WHERE driver_id = :driver_id");
                $stmt->execute(['driver_id' => $driver['driver_id']]);
                
                // Verify the update
                $checkStmt = $pdo->prepare("SELECT is_online FROM driver WHERE driver_id = :driver_id");
                $checkStmt->execute(['driver_id' => $driver['driver_id']]);
                $status = $checkStmt->fetchColumn();
                error_log("Driver {$driver['fullname']} login - Online status set to: " . $status);
                
                // Redirect to driver dashboard
                $_SESSION['driver_id'] = $driver['driver_id'];
                $_SESSION['driver_name'] = $driver['fullname'];
                header("Location: dashboardDriver.php");
                exit();
            } catch (Exception $e) {
                error_log("Driver login - Error updating online status: " . $e->getMessage());
                // Redirect to error page
                header("Location: error.php");
                exit();
            } 