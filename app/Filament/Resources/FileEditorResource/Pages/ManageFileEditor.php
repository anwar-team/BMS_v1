<?php

namespace App\Filament\Resources\FileEditorResource\Pages;

use App\Filament\Resources\FileEditorResource;
use App\Services\FileService;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions;
use Filament\Notifications\Notification;
use AbdelhamidErrahmouni\FilamentMonacoEditor\MonacoEditor;
use Illuminate\Support\Facades\File;

class ManageFileEditor extends ManageRecords
{
    protected static string $resource = FileEditorResource::class;

    protected ?string $heading = 'File Editor';

    public $selectedFile = '';
    public $fileContent = '';
    public $availableFiles = [];

    public function mount(): void
    {
        $this->loadAvailableFiles();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        // File selector
                        Forms\Components\Section::make('Select File')
                            ->schema([
                                Forms\Components\Select::make('selectedFile')
                                    ->label('Choose File to Edit')
                                    ->options($this->getFileOptions())
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn ($state) => $this->loadFileContent($state))
                                    ->placeholder('Select a file to edit...')
                            ])
                            ->columnSpan(1),
                        
                        // Monaco Editor
                        Forms\Components\Section::make('Code Editor')
                            ->schema([
                                MonacoEditor::make('fileContent')
                                    ->label('File Content')
                                    ->language(fn () => $this->getFileLanguage())
                                    ->theme('blackboard')
                                    ->fontSize('14px')
                                    ->disablePreview()
                                    ->showFullScreenToggle(true)
                                    ->placeholderText('Select a file to start editing...')
                                    ->visible(fn () => !empty($this->selectedFile))
                            ])
                            ->columnSpan(2),
                    ])
                    ->columns(3)
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Save File')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => !empty($this->selectedFile))
                ->action('saveFile')
        ];
    }

    public function saveFile()
    {
        if (empty($this->selectedFile)) {
            Notification::make()
                ->title('No file selected')
                ->danger()
                ->send();
            return;
        }

        try {
            $fileService = app(FileService::class);
            $fileService->writeFile($this->selectedFile, $this->fileContent);
            
            Notification::make()
                ->title('File saved successfully!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving file')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function loadFileContent($filePath)
    {
        if (empty($filePath)) {
            $this->fileContent = '';
            return;
        }

        try {
            $fileService = app(FileService::class);
            $this->fileContent = $fileService->readFile($filePath);
        } catch (\Exception $e) {
            $this->fileContent = '';
            Notification::make()
                ->title('Error loading file')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function loadAvailableFiles()
    {
        $this->availableFiles = [];
        $allowedPaths = [
            'app' => base_path('app'),
            'resources' => base_path('resources'),
            'config' => base_path('config'),
            'routes' => base_path('routes'),
        ];

        foreach ($allowedPaths as $type => $path) {
            if (is_dir($path)) {
                $this->scanDirectory($path, $type);
            }
        }
    }

    private function scanDirectory($directory, $prefix = '', $depth = 0)
    {
        if ($depth > 3) return; // Limit depth to avoid deep recursion

        $items = File::glob($directory . '/*');
        
        foreach ($items as $item) {
            $basename = basename($item);
            
            if (is_file($item)) {
                $extension = pathinfo($item, PATHINFO_EXTENSION);
                if (in_array($extension, ['php', 'blade.php', 'js', 'css', 'json', 'md', 'txt'])) {
                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $item);
                    $this->availableFiles[$item] = $prefix . '/' . $relativePath;
                }
            } elseif (is_dir($item) && !in_array($basename, ['.', '..', 'vendor', 'node_modules', '.git'])) {
                $this->scanDirectory($item, $prefix, $depth + 1);
            }
        }
    }

    private function getFileOptions(): array
    {
        return $this->availableFiles;
    }

    private function getFileLanguage(): string
    {
        if (empty($this->selectedFile)) {
            return 'plaintext';
        }

        $extension = pathinfo($this->selectedFile, PATHINFO_EXTENSION);
        
        return match($extension) {
            'php' => 'php',
            'js' => 'javascript',
            'css' => 'css',
            'json' => 'json',
            'md' => 'markdown',
            'html' => 'html',
            'blade.php' => 'html',
            default => 'plaintext'
        };
    }
}