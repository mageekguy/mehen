<?php

namespace mehen;

class tunnel
{
	const defaultSshPort = 22;
	const defaultMaxTry = 5;
	const sleepingDelay = 1;
	const sshTunnelingCommand = 'exec ssh -o StrictHostKeyChecking=no %1$s -p %2$s -N -L %3$s:%4$s:%5$s:%6$s %7$s@%8$s';

	protected $sshTunnelingCommand = '';
	protected $sshUser = '';
	protected $sshHost = '';
	protected $sshPort = 0;
	protected $sshPrivateKeyFile = null;
	protected $remoteHost = '';
	protected $remotePort = 0;
	protected $localHost = '';
	protected $localPort = 0;
	protected $maxTry = 5;
	protected $resource = null;

	public function __construct($sshHost, $sshUser, $localHost, $localPort, $remoteHost, $remotePort)
	{
		$this->sshHost = $sshHost;
		$this->sshUser = $sshUser;
		$this->remoteHost = $remoteHost;
		$this->remotePort = $remotePort;
		$this->localHost = $localHost;
		$this->localPort = $localPort;

		$this
			->setSshPort()
			->setMaxTry()
		;
	}

	public function __destruct()
	{
		$this->close();
	}

	public function __toString()
	{
		return $this->getSshTunnelingCommand();
	}

	public function getSshHost()
	{
		return $this->sshHost;
	}

	public function getSshUser()
	{
		return $this->sshUser;
	}

	public function getLocalHost()
	{
		return $this->localHost;
	}

	public function getLocalPort()
	{
		return $this->localPort;
	}

	public function getRemoteHost()
	{
		return $this->remoteHost;
	}

	public function getRemotePort()
	{
		return $this->remotePort;
	}

	public function getMaxTry()
	{
		return $this->maxTry;
	}

	public function setMaxTry($maxTry = null)
	{
		$this->maxTry = $maxTry ?: static::defaultMaxTry;

		return $this;
	}

	public function getSshPort()
	{
		return $this->sshPort;
	}

	public function setSshPort($port = null)
	{
		$this->sshPort = $port ?: static::defaultSshPort;

		return $this;
	}

	public function getSshTunnelingCommand()
	{
		return sprintf(static::sshTunnelingCommand,
			$this->sshPrivateKeyFile === null ? '' : '-i ' . $this->sshPrivateKeyFile,
			$this->sshPort,
			$this->localHost,
			$this->localPort,
			$this->remoteHost,
			$this->remotePort,
			$this->sshUser,
			$this->sshHost
		);
	}

	public function setSshPrivateKeyFile($privateKeyFile)
	{
		$this->sshPrivateKeyFile = $privateKeyFile;

		return $this;
	}

	public function open()
	{
		if ($this->isOpen() === true)
		{
			throw new tunnel\exception('Tunnel is already open');
		}

		$sshCommand = sprintf($this->sshTunnelingCommand,
			$this->sshPrivateKeyFile === null ? '' : '-i ' . $this->sshPrivateKeyFile,
			$this->sshPort,
			$this->localHost,
			$this->localPort,
			$this->remoteHost,
			$this->remotePort,
			$this->sshUser,
			$this->sshHost
		);

		$descriptors = array();
		$pipes = array();

		$resource = proc_open((string) $this, $descriptors, $pipes);

		if ($resource === false)
		{
			throw new tunnel\exception('Unable to execute \'' . $this . '\'');
		}

		$this->resource = $resource;

		$maxTry = $this->maxTry;

		while ($this->isOpen() === false && $maxTry > 0)
		{
			$maxTry--;

			sleep(static::sleepingDelay);
		}

		if ($this->isOpen() === false)
		{
			$this->close();

			throw new tunnel\exception('Unable to open tunnel');
		}

		return $this;
	}

	public function close()
	{
		if ($this->resource !== null)
		{
			proc_terminate($this->resource, SIGKILL);
			proc_close($this->resource);

			$this->resource = null;
		}

		return $this;
	}

	public function isOpen()
	{
		$socket = stream_socket_client('tcp://' . $this->localHost . ':' . $this->localPort, $errorNumber, $errorMessage, 1);

		$isOpen = ($socket !== false);

		if ($isOpen === true)
		{
			fclose($socket);
		}

		return $isOpen;
	}
}
