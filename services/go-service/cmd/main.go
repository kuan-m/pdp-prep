package main

import (
	"context"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"pdp-prep/services/go-service/internal/handler"
)

const (
	defaultPort = ":8090"
)

func main() {
	// Get port from environment or use default
	port := os.Getenv("PORT")
	if port == "" {
		port = defaultPort
	}
	if port[0] != ':' {
		port = ":" + port
	}

	log.Printf("Starting Go test service on port %s", port)

	// Create HTTP server
	mux := http.NewServeMux()

	// Register handlers
	mux.HandleFunc("/test", handler.HandleTest)
	mux.HandleFunc("/test-light", handler.HandleTestLight)

	// Health check endpoint
	mux.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		w.Write([]byte("OK"))
	})

	server := &http.Server{
		Addr:    port,
		Handler: mux,
	}

	// Start server in goroutine
	serverErrors := make(chan error, 1)
	go func() {
		log.Printf("Server listening on %s", port)
		serverErrors <- server.ListenAndServe()
	}()

	// Wait for interrupt signal
	shutdown := make(chan os.Signal, 1)
	signal.Notify(shutdown, os.Interrupt, syscall.SIGTERM)

	select {
	case err := <-serverErrors:
		log.Fatalf("Error starting server: %v", err)
	case sig := <-shutdown:
		log.Printf("Received signal: %v, shutting down gracefully...", sig)

		ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
		defer cancel()

		if err := server.Shutdown(ctx); err != nil {
			log.Printf("Error during server shutdown: %v", err)
			server.Close()
		}

		log.Println("Server stopped")
	}
}
